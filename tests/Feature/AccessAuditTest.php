<?php

namespace Tests\Feature;

use App\Models\AccessAuditEvent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AccessAuditTest extends TestCase
{
    use RefreshDatabase;

    private function workspace(): Client
    {
        return Client::create([
            'company_name' => 'Audit Workspace',
            'contact_name' => 'Contact',
            'email' => 'audit@example.test',
            'status' => 'Active',
        ]);
    }

    private function member(Client $client, string $role): User
    {
        $user = User::factory()->create();
        $client->members()->attach($user->id, ['role' => $role]);
        return $user;
    }

    public function test_web_role_change_and_removal_record_actor_and_roles(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $member = $this->member($client, 'viewer');
        $url = '/clients/'.$client->id.'/members/'.$member->id;

        $this->actingAs($admin)->patch($url, ['role' => 'editor'])
            ->assertRedirect();

        $this->assertDatabaseHas('access_audit_events', [
            'client_id' => $client->id,
            'actor_id' => $admin->id,
            'subject_user_id' => $member->id,
            'source' => 'web',
            'action' => 'workspace.role_changed',
            'previous_role' => 'viewer',
            'new_role' => 'editor',
        ]);

        $this->patch($url, ['role' => 'editor'])->assertRedirect();
        $this->assertDatabaseCount('access_audit_events', 1);

        $this->delete($url)->assertRedirect();
        $this->assertDatabaseHas('access_audit_events', [
            'client_id' => $client->id,
            'actor_id' => $admin->id,
            'subject_user_id' => $member->id,
            'action' => 'workspace.member_removed',
            'previous_role' => 'editor',
            'new_role' => null,
        ]);
        $this->assertNull($member->workspaceRole($client));
        $this->assertDatabaseCount('access_audit_events', 2);
    }

    public function test_forbidden_and_last_administrator_changes_create_no_events(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $viewer = $this->member($client, 'viewer');
        $url = '/clients/'.$client->id.'/members/'.$admin->id;

        $this->actingAs($viewer)->patch($url, ['role' => 'viewer'])
            ->assertForbidden();

        $this->actingAs($admin)->patch($url, ['role' => 'viewer'])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseCount('access_audit_events', 0);
        $this->assertSame('administrator', $admin->workspaceRole($client));
    }

    public function test_cli_records_changes_without_claiming_a_web_actor(): void
    {
        $client = $this->workspace();
        $user = User::factory()->create();

        $arguments = [
            'email' => $user->email,
            '--workspace' => $client->id,
            '--role' => 'editor',
        ];

        $this->artisan('bos:access', $arguments)->assertExitCode(0);
        $this->artisan('bos:access', $arguments)->assertExitCode(0);

        $this->assertDatabaseCount('access_audit_events', 1);
        $this->assertDatabaseHas('access_audit_events', [
            'client_id' => $client->id,
            'subject_user_id' => $user->id,
            'actor_id' => null,
            'source' => 'cli',
            'action' => 'workspace.member_added',
            'previous_role' => null,
            'new_role' => 'editor',
        ]);

        $this->artisan('bos:access', array_merge($arguments, [
            '--revoke' => true,
        ]))->assertExitCode(0);

        $this->assertNull($user->workspaceRole($client));
        $this->assertDatabaseHas('access_audit_events', [
            'subject_user_id' => $user->id,
            'source' => 'cli',
            'action' => 'workspace.member_removed',
            'previous_role' => 'editor',
            'new_role' => null,
        ]);

        $this->artisan('bos:access', [
            'email' => $user->email,
            '--platform-admin' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('access_audit_events', [
            'client_id' => null,
            'subject_user_id' => $user->id,
            'action' => 'platform.admin_granted',
            'new_role' => 'platform_admin',
        ]);
    }


    public function test_invitation_lifecycle_records_events_without_tokens(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $invitee = User::factory()->create();
        $endpoint = '/clients/'.$client->id.'/invitations';

        $this->actingAs($admin)->post($endpoint, [
            'email' => $invitee->email,
            'role' => 'viewer',
        ])->assertRedirect();

        $first = \App\Models\WorkspaceInvitation::latest('id')->firstOrFail();

        $this->assertDatabaseHas('access_audit_events', [
            'invitation_id' => $first->id,
            'actor_id' => $admin->id,
            'action' => 'invitation.created',
            'new_role' => 'viewer',
        ]);

        $this->post($endpoint, [
            'email' => $invitee->email,
            'role' => 'editor',
        ])->assertRedirect();

        $second = \App\Models\WorkspaceInvitation::latest('id')->firstOrFail();

        $this->assertDatabaseHas('access_audit_events', [
            'invitation_id' => $first->id,
            'action' => 'invitation.replaced',
            'previous_role' => 'viewer',
        ]);
        $this->assertNotNull($first->fresh()->revoked_at);

        // Use a known test token to exercise acceptance.
        $token = bin2hex(random_bytes(32));
        $second->update(['token_hash' => hash('sha256', $token)]);

        $this->actingAs($invitee)
            ->post('/workspace-invitations/'.$token)
            ->assertRedirect(route('clients.show', $client));

        $this->assertSame('editor', $invitee->workspaceRole($client));
        $this->assertDatabaseHas('access_audit_events', [
            'invitation_id' => $second->id,
            'actor_id' => $invitee->id,
            'subject_user_id' => $invitee->id,
            'action' => 'invitation.accepted',
            'new_role' => 'editor',
        ]);
        $this->assertDatabaseHas('access_audit_events', [
            'invitation_id' => $second->id,
            'subject_user_id' => $invitee->id,
            'action' => 'workspace.member_added',
        ]);

        $count = AccessAuditEvent::count();
        $this->post('/workspace-invitations/'.$token)->assertStatus(410);
        $this->assertSame($count, AccessAuditEvent::count());

        $this->actingAs($admin)->post($endpoint, [
            'email' => 'cancel-audit@example.test',
            'role' => 'viewer',
        ])->assertRedirect();

        $pending = \App\Models\WorkspaceInvitation::latest('id')->firstOrFail();
        $cancelUrl = $endpoint.'/'.$pending->id;

        $this->delete($cancelUrl)->assertRedirect();
        $this->assertDatabaseHas('access_audit_events', [
            'invitation_id' => $pending->id,
            'actor_id' => $admin->id,
            'action' => 'invitation.cancelled',
        ]);

        $count = AccessAuditEvent::count();
        $this->delete($cancelUrl)->assertRedirect();
        $this->assertSame($count, AccessAuditEvent::count());

        $records = AccessAuditEvent::all()->toJson();
        $this->assertStringNotContainsString($token, $records);
        $this->assertStringNotContainsString(hash('sha256', $token), $records);
        $this->assertStringNotContainsString('password', $records);
    }


    public function test_history_is_admin_only_scoped_and_paginated(): void
    {
        $client = $this->workspace();
        $other = Client::create([
            'company_name' => 'Other Workspace',
            'contact_name' => 'Other Contact',
            'email' => 'other-audit@example.test',
            'status' => 'Active',
        ]);

        $admin = $this->member($client, 'administrator');
        $viewer = $this->member($client, 'viewer');
        $editor = $this->member($client, 'editor');
        $url = '/clients/'.$client->id.'/access-history';

        for ($i = 0; $i < 26; $i++) {
            AccessAuditEvent::create([
                'client_id' => $client->id,
                'actor_id' => $admin->id,
                'subject_user_id' => $viewer->id,
                'source' => 'web',
                'action' => 'workspace.role_changed',
                'previous_role' => 'viewer',
                'new_role' => 'editor',
                'created_at' => now(),
            ]);
        }

        AccessAuditEvent::create([
            'client_id' => $other->id,
            'source' => 'cli',
            'action' => 'foreign_workspace_marker',
            'created_at' => now(),
        ]);

        AccessAuditEvent::create([
            'source' => 'cli',
            'action' => 'platform.admin_granted',
            'created_at' => now(),
        ]);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($viewer)->get($url)->assertForbidden();
        $this->actingAs($editor)->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create())->get($url)->assertNotFound();

        $response = $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertViewIs('clients.access-history')
            ->assertSee('Workspace access history')
            ->assertSee('Member role changed')
            ->assertSee('Page 1 of 2')
            ->assertDontSee('foreign_workspace_marker')
            ->assertDontSee('platform.admin_granted');

        $this->assertStringContainsString(
            'no-store', $response->headers->get('Cache-Control')
        );

        $events = $response->viewData('events');
        $this->assertCount(25, $events->items());
        $this->assertSame(26, $events->total());

        $this->get($url.'?page=2')
            ->assertOk()
            ->assertSee('Page 2 of 2')
            ->assertViewHas('events', fn ($events) => count($events->items()) === 1);

        $this->get('/clients/'.$other->id.'/access-history')->assertNotFound();

        $platformAdmin = User::factory()->create();
        $platformAdmin->forceFill(['is_platform_admin' => true])->save();

        $this->actingAs($platformAdmin)->get($url)
            ->assertOk()
            ->assertDontSee('foreign_workspace_marker');
    }

    public function test_access_change_rolls_back_when_audit_creation_fails(): void
    {
        $client = $this->workspace();
        $admin = $this->member($client, 'administrator');
        $member = $this->member($client, 'viewer');

        $event = 'eloquent.creating: '.AccessAuditEvent::class;
        Event::listen($event, function () {
            throw new \RuntimeException('Simulated audit failure');
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->patch(
                '/clients/'.$client->id.'/members/'.$member->id,
                ['role' => 'editor']
            );
            $this->fail('Expected the audit failure.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Simulated audit failure', $error->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertSame('viewer', $member->workspaceRole($client));
        $this->assertDatabaseCount('access_audit_events', 0);
    }
}
