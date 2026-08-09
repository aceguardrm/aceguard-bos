<?php

namespace App\Http\Controllers;

use App\Models\Client;
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
        $client = Client::first();

        $health = null;
        $security = null;
        $pulse = null;

        if ($client) {
            $health = $businessHealthService->calculate($client);

            $security = $securityScoreService->calculate($client);

            $pulse = $businessPulseService->calculate($client);
        }

        return view('dashboard.index', [
            'client' => $client,
            'health' => $health,
            'security' => $security,
            'pulse' => $pulse,
        ]);
    }
}