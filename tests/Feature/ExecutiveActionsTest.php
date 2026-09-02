<?php

namespace Tests\Feature;

use App\Models\BusinessPulseAssessment;
use App\Models\Client;
use App\Models\Project;
use App\Models\SecurityControl;
use App\Models\User;
use App\Services\BusinessPulseService;
use App\Services\ExecutiveActionService;
use App\Services\SecurityScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExecutiveActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_ranks_actions_across_all_four_bos_domains(): void
    {
        Carbon::setTestNow('2026-09-02 10:00:00');

        $client = $this->createClient();

        SecurityControl::create([
            'client_id' => $client->id,
            'category' => 'Microsoft 365',
            'control' => 'Microsoft 365 Security',
            'enabled' => false,
            'points' => 0,
            'maximum_points' => 15,
        ]);

        SecurityControl::create([
            'client_id' => $client->id,
            'category' => 'Backup',
            'control' => 'Verified Backups',
            'enabled' => false,
            'points' => 0,
            'maximum_points' => 10,
        ]);

        BusinessPulseAssessment::create([
            'client_id' => $client->id,
            'operations_score' => 35,
            'continuity_score' => 80,
            'documentation_score' => 80,
            'compliance_score' => 80,
            'technology_score' => 80,
            'readiness_score' => 80,
            'overall_score' => 73,
            'status' => 'completed',
            'assessed_at' => now(),
        ]);

        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Email Migration',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => now()->subDays(3),
            'progress' => 40,
        ]);

        $security = app(SecurityScoreService::class)->calculate($client);
        $pulse = app(BusinessPulseService::class)->calculate($client);

        $result = app(ExecutiveActionService::class)->build(
            $client,
            collect([$project]),
            $security,
            $pulse
        );

        $this->assertSame(4, $result['total']);
        $this->assertSame('projects', $result['actions']->first()['domain']);
        $this->assertSame('critical', $result['actions']->first()['severity']);
        $this->assertSame(
            [
                'projects' => 1,
                'cyber' => 1,
                'business' => 1,
                'microsoft_365' => 1,
            ],
            $result['domains']->pluck('count', 'key')->all()
        );
    }

    public function test_dashboard_exposes_ranked_actions_and_resolution_links(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient();

        $control = SecurityControl::create([
            'client_id' => $client->id,
            'category' => 'Microsoft 365',
            'control' => 'Microsoft 365 Security',
            'enabled' => false,
            'points' => 0,
            'maximum_points' => 15,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Management Action Centre')
            ->assertSee('Microsoft 365 Security')
            ->assertSee('Resolve Control')
            ->assertSee(
                route('security.workspace', $client).'#control-'.$control->id,
                false
            );
    }

    private function createClient(): Client
    {
        return Client::create([
            'company_name' => 'AceGuard Test Client',
            'contact_name' => 'Test Contact',
            'email' => 'client@example.test',
            'status' => 'Active',
        ]);
    }
}
