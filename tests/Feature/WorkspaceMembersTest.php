<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Notifications\WorkspaceInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WorkspaceMembersTest extends TestCase
{
    use RefreshDatabase;

    private function workspace(): Client
    {
        return Client::create([
            'company_name' => 'Test Workspace',
            'contact_name' => 'Contact',
            'email' => 'workspace@example.test',
            'status' => 'Active',
        ]);
    }

    private function member(Client $client, string $role): User
    {
        $user = User::factory()->create();
        $client->members()->attach($user, ['role' => $role]);
        return $user;
    }

    private function invitation(Client $client, User $admin, string $email): array
    {
        $token = bin2hex(random_bytes(32));
        $invitation = WorkspaceInvitation::create([
            'client_id' => $client->id,
            'invited_by' => $admin->id,
            'email' => $email,
            'role' => 'viewer',
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
        ]);
        return [$invitation, '/workspace-invitations/'.$token];
    }

    public function test_only_workspace_administrators_can_manage_members(): void
    {
        $client = $this->workspace();
        $viewer = $this->member($client, 'viewer');
        $this->actingAs($viewer)->get('/clients/'.$client->id.'/members')->assertForbidden();
        $this->post('/clients/'.$client->id.'/invitations', [
            'email' => 'new@example.test', 'role' => 'viewer',
        ])->assertForbidden();
        $this->assertDatabaseCount('workspace_invitations', 0);

        $this->actingAs(User::factory()->create())
            ->get('/clients/'.$client->id.'/members')->assertNotFound();

        $admin = $this->member($client, 'administrator');
        $this->actingAs($admin)->get('/clients/'.$client->id.'/members')
            ->assertOk()->assertSee('Send invitation')->assertSee($viewer->email);
    }

    public function test_self_demotion_redirects_to_workspace_and_removes_management_access(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $otherAdmin = $this->member($client, 'administrator');

        $this->actingAs($admin)
            ->patch('/clients/'.$client->id.'/members/'.$admin->id, ['role' => 'viewer'])
            ->assertRedirect(route('clients.show', $client));

        $this->assertSame('viewer', $admin->workspaceRole($client));
        $this->assertSame('administrator', $otherAdmin->workspaceRole($client));
        $this->get('/clients/'.$client->id)->assertOk();
        $this->get('/clients/'.$client->id.'/members')->assertForbidden();
        $this->patch('/clients/'.$client->id.'/members/'.$otherAdmin->id, [
            'role' => 'viewer',
        ])->assertForbidden();
    }

    public function test_last_administrator_cannot_be_demoted_or_removed(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $url = '/clients/'.$client->id.'/members/'.$admin->id;
        $this->actingAs($admin)->patch($url, ['role' => 'viewer'])
            ->assertSessionHasErrors('role');
        $this->delete($url)->assertSessionHasErrors('role');
        $this->assertSame('administrator', $admin->workspaceRole($client));
    }

    public function test_invitation_is_sent_and_replacement_revokes_old_link(): void
    {
        Notification::fake();
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        [$old] = $this->invitation($client, $admin, 'new@example.test');
        $this->actingAs($admin)->post('/clients/'.$client->id.'/invitations', [
            'email' => 'new@example.test', 'role' => 'editor',
        ])->assertRedirect(route('clients.members.index', $client));
        $this->assertNotNull($old->fresh()->revoked_at);
        Notification::assertSentOnDemand(WorkspaceInvitationNotification::class);
        $latest = WorkspaceInvitation::latest('id')->first();
        $this->assertSame('editor', $latest->role);
        $this->assertSame(64, strlen($latest->token_hash));
        $this->assertArrayNotHasKey('token_hash', $latest->toArray());
    }

    public function test_new_account_acceptance_is_single_use_and_does_not_log_in(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        [$invitation, $url] = $this->invitation($client, $admin, 'new@example.test');
        $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $this->post($url, [
            'name' => 'New Member',
            'password' => 'A-long-test-password-123',
            'password_confirmation' => 'A-long-test-password-123',
            'email' => 'attacker@example.test',
            'role' => 'administrator',
            'is_platform_admin' => true,
        ])->assertRedirect(route('login'));
        $this->assertGuest();
        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('A-long-test-password-123', $user->password));
        $this->assertFalse($user->is_platform_admin);
        $this->assertSame('viewer', $user->workspaceRole($client));
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->post($url, [])->assertStatus(410);
    }

    public function test_existing_account_requires_login_and_matching_email(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $invitee = User::factory()->create();
        [$invitation, $url] = $this->invitation($client, $admin, strtolower($invitee->email));
        $this->post($url, [])->assertRedirect(route('login'));
        $this->assertNull($invitation->fresh()->accepted_at);
        $this->actingAs($admin)->post($url, [])->assertForbidden();
        $this->actingAs($invitee)->post($url, [])
            ->assertRedirect(route('clients.show', $client));
        $this->assertSame('viewer', $invitee->workspaceRole($client));
    }

    public function test_expired_cancelled_and_revoked_sender_invitations_cannot_be_accepted(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        [$expired, $expiredUrl] = $this->invitation($client, $admin, 'expired@example.test');
        $expired->update(['expires_at' => now()->subMinute()]);
        $this->get($expiredUrl)->assertStatus(410);

        [$cancelled, $cancelledUrl] = $this->invitation($client, $admin, 'cancelled@example.test');
        $cancelled->update(['revoked_at' => now()]);
        $this->post($cancelledUrl, [])->assertStatus(410);

        [$pending, $pendingUrl] = $this->invitation($client, $admin, 'pending@example.test');
        $client->members()->detach($admin);
        $this->post($pendingUrl, [
            'name' => 'Pending',
            'password' => 'A-long-test-password-123',
            'password_confirmation' => 'A-long-test-password-123',
        ])->assertStatus(410);
        $this->assertDatabaseMissing('users', ['email' => 'pending@example.test']);
        $this->assertNull($pending->fresh()->accepted_at);
    }
}
