<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Services\BusinessHealthService;
use App\Services\BusinessPulseService;
use App\Services\SecurityScoreService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        BusinessHealthService $businessHealthService,
        SecurityScoreService $securityScoreService,
        BusinessPulseService $businessPulseService
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Primary Workspace Intelligence
        |--------------------------------------------------------------------------
        */

        $client = Client::first();

        $health = null;
        $security = null;
        $pulse = null;

        if ($client) {
            $health = $businessHealthService->calculate($client);

            $security = $securityScoreService->calculate($client);

            $pulse = $businessPulseService->calculate($client);
        }


        /*
        |--------------------------------------------------------------------------
        | Project Delivery Intelligence
        |--------------------------------------------------------------------------
        |
        | Executive delivery metrics are calculated from the complete
        | project portfolio using the same Delivery Health engine used
        | throughout AceGuard BOS.
        |
        */

        $portfolioProjects = Project::with('tasks')
            ->latest()
            ->get();

        $totalProjects =
            $portfolioProjects->count();

        $activeProjects =
            $portfolioProjects
                ->whereNotIn(
                    'status',
                    ['completed', 'cancelled']
                )
                ->count();

        $healthyProjects =
            $portfolioProjects
                ->filter(
                    fn ($project) =>
                        $project->deliveryHealthKey()
                        === 'healthy'
                )
                ->count();

        $attentionProjects =
            $portfolioProjects
                ->filter(
                    fn ($project) =>
                        $project->deliveryHealthKey()
                        === 'attention'
                )
                ->count();

        $atRiskProjects =
            $portfolioProjects
                ->filter(
                    fn ($project) =>
                        $project->deliveryHealthKey()
                        === 'at_risk'
                )
                ->count();

        $overdueProjects =
            $portfolioProjects
                ->filter(
                    fn ($project) =>
                        $project->isProjectOverdue()
                )
                ->count();

        $dueSoonProjects =
            $portfolioProjects
                ->filter(function ($project) {

                    if (
                        !$project->due_date
                        || in_array(
                            $project->status,
                            ['completed', 'cancelled']
                        )
                    ) {
                        return false;
                    }

                    return $project->due_date
                        ->copy()
                        ->startOfDay()
                        ->between(
                            now()->startOfDay(),
                            now()
                                ->copy()
                                ->addDays(7)
                                ->endOfDay()
                        );
                })
                ->count();

        $averageDeliveryHealth =
            $totalProjects > 0
                ? (int) round(
                    $portfolioProjects
                        ->avg(
                            fn ($project) =>
                                $project->deliveryHealthScore()
                        )
                )
                : 0;

        $priorityDeliveryProjects =
            $portfolioProjects
                ->filter(
                    fn ($project) =>
                        in_array(
                            $project->deliveryHealthKey(),
                            ['attention', 'at_risk']
                        )
                )
                ->sortBy(
                    fn ($project) =>
                        $project->deliveryHealthScore()
                )
                ->take(5)
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Executive Dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', [
            'client' => $client,
            'health' => $health,
            'security' => $security,
            'pulse' => $pulse,

            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'healthyProjects' => $healthyProjects,
            'attentionProjects' => $attentionProjects,
            'atRiskProjects' => $atRiskProjects,
            'overdueProjects' => $overdueProjects,
            'dueSoonProjects' => $dueSoonProjects,
            'averageDeliveryHealth' => $averageDeliveryHealth,
            'priorityDeliveryProjects' => $priorityDeliveryProjects,
        ]);
    }
}
