<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\SecurityControl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceAccessTest extends TestCase
{
    use RefreshDatabase;

    private function workspace(string $name): Client
    {
        return Client::create(['company_name' => $name, 'contact_name' => 'Contact',
            'email' => strtolower($name).'@example.test', 'status' => 'Active']);
    }

    private function member(Client $client, string $role): User
    {
        $user = User::factory()->create();
        $client->members()->attach($user, ['role' => $role]);
        return $user;
    }

    private function project(Client $client, string $name = 'Private project'): Project
    {
        return $client->projects()->create(['name' => $name, 'status' => 'planned',
            'priority' => 'medium', 'progress' => 0]);
    }

    public function test_unassigned_user_sees_no_company_data(): void
    {
        $client = $this->workspace('SecretCompany');
        $this->project($client);
        $this->actingAs(User::factory()->create());
        $this->get('/clients')->assertOk()->assertDontSee('SecretCompany');
        $this->get('/projects')->assertOk()->assertDontSee('Private project');
        $this->get('/dashboard')->assertOk()->assertViewHas('client', null)
            ->assertViewHas('totalProjects', 0);
        $this->get('/clients/'.$client->id)->assertNotFound();
    }

    public function test_members_only_see_assigned_companies_and_portfolio_counts(): void
    {
        $foreign = $this->workspace('ForeignCompany');
        $own = $this->workspace('OwnCompany');
        $this->project($foreign, 'Secret foreign project');
        $project = $this->project($own, 'Own project');
        $this->actingAs($this->member($own, 'viewer'));
        $this->get('/clients')->assertOk()->assertSee('OwnCompany')->assertDontSee('ForeignCompany');
        $this->get('/projects')->assertOk()->assertSee('Own project')->assertDontSee('Secret foreign project');
        $this->get('/dashboard')->assertOk()->assertViewHas('client', fn ($c) => $c->is($own))
            ->assertViewHas('totalProjects', 1);
        $this->get('/projects/'.$project->id)->assertOk();
        $this->get('/projects?client='.$foreign->id)->assertNotFound();
    }

    public function test_foreign_workspace_routes_and_nested_records_are_denied(): void
    {
        $own = $this->workspace('Own');
        $foreign = $this->workspace('Foreign');
        $project = $this->project($foreign);
        $this->actingAs($this->member($own, 'administrator'));
        foreach (['', '/edit', '/security', '/business-pulse'] as $suffix) {
            $this->get('/clients/'.$foreign->id.$suffix)->assertNotFound();
        }
        $this->get('/projects/'.$project->id)->assertNotFound();
        $this->patch('/projects/'.$project->id, [])->assertNotFound();
        $this->delete('/projects/'.$project->id)->assertNotFound();
        $this->post('/projects/'.$project->id.'/tasks', [])->assertNotFound();
        $this->patch('/clients/'.$foreign->id.'/business-pulse', [])->assertNotFound();
    }

    public function test_viewer_cannot_write_or_access_edit_forms(): void
    {
        $client = $this->workspace('Own');
        $project = $this->project($client);
        $this->actingAs($this->member($client, 'viewer'));
        $this->get('/clients/'.$client->id.'/edit')->assertForbidden();
        $this->get('/projects/'.$project->id.'/edit')->assertForbidden();
        $this->patch('/clients/'.$client->id, [])->assertForbidden();
        $this->patch('/projects/'.$project->id, [])->assertForbidden();
        $this->delete('/projects/'.$project->id)->assertForbidden();
        $this->post('/projects/'.$project->id.'/tasks', [])->assertForbidden();
        $this->patch('/clients/'.$client->id.'/business-pulse', [])->assertForbidden();
        $this->get('/projects/create')->assertForbidden();
    }

    public function test_editor_can_create_project_but_cannot_edit_company_or_create_workspace(): void
    {
        $client = $this->workspace('Own');
        $this->actingAs($this->member($client, 'editor'));
        $this->post('/projects', ['client_id' => $client->id, 'name' => 'Allowed',
            'status' => 'planned', 'priority' => 'medium', 'progress' => 0])->assertRedirect();
        $this->assertDatabaseHas('projects', ['name' => 'Allowed', 'client_id' => $client->id]);
        $this->get('/clients/'.$client->id.'/edit')->assertForbidden();
        $this->post('/clients', [])->assertForbidden();
        $this->delete('/clients/'.$client->id)->assertForbidden();
    }

    public function test_submitted_workspace_ids_cannot_move_or_create_data_in_another_company(): void
    {
        $own = $this->workspace('Own');
        $foreign = $this->workspace('Foreign');
        $project = $this->project($own);
        $this->actingAs($this->member($own, 'editor'));
        $payload = ['client_id' => $foreign->id, 'name' => 'Attack',
            'status' => 'planned', 'priority' => 'medium', 'progress' => 0];
        $this->post('/projects', $payload)->assertNotFound();
        $this->patch('/projects/'.$project->id, $payload)->assertNotFound();
        $this->assertSame($own->id, $project->fresh()->client_id);
        $this->assertDatabaseMissing('projects', ['name' => 'Attack']);
    }

    public function test_editor_cannot_move_project_to_a_view_only_workspace(): void
    {
        $own = $this->workspace('Own');
        $other = $this->workspace('Other');
        $project = $this->project($own);
        $user = $this->member($own, 'editor');
        $other->members()->attach($user, ['role' => 'viewer']);
        $this->actingAs($user)->patch('/projects/'.$project->id,
            ['client_id' => $other->id])->assertForbidden();
    }

    public function test_nested_task_and_security_control_must_belong_to_the_authorized_parent(): void
    {
        $own = $this->workspace('Own');
        $foreign = $this->workspace('Foreign');
        $project = $this->project($own);
        $otherProject = $this->project($foreign);
        $task = $otherProject->tasks()->create(['title' => 'Foreign task', 'status' => 'pending', 'priority' => 'medium']);
        $control = SecurityControl::create(['client_id' => $foreign->id, 'category' => 'Backup',
            'control' => 'Secret control', 'enabled' => false, 'points' => 0, 'maximum_points' => 10]);
        $this->actingAs($this->member($own, 'editor'));
        $this->patch('/projects/'.$project->id.'/tasks/'.$task->id.'/toggle')->assertNotFound();
        $this->delete('/projects/'.$project->id.'/tasks/'.$task->id)->assertNotFound();
        $this->patch('/clients/'.$own->id.'/security-controls/'.$control->id, ['enabled' => true])->assertNotFound();
        $this->assertFalse($control->fresh()->enabled);
        $this->assertSame('pending', $task->fresh()->status);
    }

    public function test_revoked_membership_takes_effect_on_next_request(): void
    {
        $client = $this->workspace('Own');
        $user = $this->member($client, 'viewer');
        $this->actingAs($user)->get('/clients/'.$client->id)->assertOk();
        $client->members()->detach($user);
        $this->get('/clients/'.$client->id)->assertNotFound();
    }

    public function test_platform_access_requires_explicit_operator_grant_and_is_not_mass_assignable(): void
    {
        $user = User::factory()->create();
        $user->refresh()->fill(['is_platform_admin' => true]);
        $this->assertFalse($user->is_platform_admin);
        $this->artisan('bos:access', ['email' => $user->email, '--platform-admin' => true])->assertSuccessful();
        $this->assertTrue($user->fresh()->is_platform_admin);
        $this->actingAs($user->fresh())->get('/clients/create')->assertOk();
        $this->artisan('bos:access', ['email' => $user->email, '--platform-admin' => true, '--revoke' => true])->assertSuccessful();
        $this->assertFalse($user->fresh()->is_platform_admin);
    }

    public function test_read_only_security_and_assessment_pages_render_without_write_controls(): void
    {
        $client = $this->workspace('Own');
        SecurityControl::create(['client_id' => $client->id, 'category' => 'Backup',
            'control' => 'Backup control', 'enabled' => false, 'points' => 0, 'maximum_points' => 10]);
        $this->actingAs($this->member($client, 'viewer'));
        $this->get('/clients/'.$client->id.'/security')->assertOk()->assertSee('disabled', false);
        $this->get('/clients/'.$client->id.'/business-pulse')->assertOk()->assertSee('disabled', false);
        $this->get('/clients')->assertOk()->assertDontSee(route('clients.create'), false);
        $this->assertDatabaseCount('business_pulse_assessments', 0);
    }

    public function test_operator_can_assign_and_revoke_a_workspace_role(): void
    {
        $client = $this->workspace('Own');
        $user = User::factory()->create();
        $this->artisan('bos:access', ['email' => $user->email, '--workspace' => $client->id,
            '--role' => 'administrator'])->assertSuccessful();
        $this->assertTrue($user->canAccessWorkspace($client, 'admin'));
        $this->artisan('bos:access', ['email' => $user->email, '--workspace' => $client->id,
            '--role' => 'invalid'])->assertFailed();
        $this->artisan('bos:access', ['email' => $user->email, '--workspace' => $client->id,
            '--revoke' => true])->assertSuccessful();
        $this->assertFalse($user->canAccessWorkspace($client));
    }
}
