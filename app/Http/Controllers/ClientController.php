<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\BusinessPulseService;
use App\Services\SecurityScoreService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);

        return view(
            'clients.index',
            compact('clients')
        );
    }


    public function create()
    {
        return view('clients.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|max:255',
            'contact_name' => 'required|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|max:30',
            'website' => 'nullable|max:255',
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:100',
            'postcode' => 'nullable|max:20',
            'country' => 'nullable|max:100',
            'status' => 'required',
            'notes' => 'nullable',
        ]);

        Client::create($validated);

        return redirect()
            ->route('clients.index')
            ->with(
                'success',
                'Client created successfully.'
            );
    }


    /**
     * Organisation Workspace
     */
    public function show(
        Client $client,
        BusinessPulseService $businessPulseService,
        SecurityScoreService $securityScoreService
    ) {
        /*
        |--------------------------------------------------------------------------
        | Business Pulse™ Intelligence
        |--------------------------------------------------------------------------
        */

        $pulse = $businessPulseService->calculate(
            $client
        );


        /*
        |--------------------------------------------------------------------------
        | Security Intelligence
        |--------------------------------------------------------------------------
        */

        $security = $securityScoreService->calculate(
            $client
        );


        /*
        |--------------------------------------------------------------------------
        | Core Scores
        |--------------------------------------------------------------------------
        */

        $businessPulseScore =
            (int) (
                $pulse['score']
                ?? 0
            );


        $businessPulseRating =
            $pulse['rating']
            ?? 'Not assessed';


        $businessHealthScore =
            (int) (
                $pulse['components']['business_health']['score']
                ?? 0
            );


        $securityScore =
            (int) (
                $security['score']
                ?? 0
            );


        $securityRating =
            $security['rating']
            ?? 'Not assessed';


        /*
        |--------------------------------------------------------------------------
        | Priorities
        |--------------------------------------------------------------------------
        */

        $priorities =
            collect(
                $pulse['priorities']
                ?? []
            );


        $priorityCount =
            (int) (
                $pulse['priority_count']
                ?? $priorities->count()
            );


        /*
        |--------------------------------------------------------------------------
        | Business Domains
        |--------------------------------------------------------------------------
        */

        $businessDomains =
            collect(
                $pulse['business_domains']
                ?? []
            );


        /*
        |--------------------------------------------------------------------------
        | Workspace Summary
        |--------------------------------------------------------------------------
        |
        | These remain zero until Projects, Documents and Planner
        | modules are implemented.
        |
        */

        $openProjects = 0;
        $documentsCount = 0;
        $appointmentsCount = 0;


        return view(
            'clients.show',
            [
                'client' =>
                    $client,

                'pulse' =>
                    $pulse,

                'security' =>
                    $security,

                'businessPulseScore' =>
                    $businessPulseScore,

                'businessPulseRating' =>
                    $businessPulseRating,

                'businessHealthScore' =>
                    $businessHealthScore,

                'securityScore' =>
                    $securityScore,

                'securityRating' =>
                    $securityRating,

                'priorities' =>
                    $priorities,

                'priorityCount' =>
                    $priorityCount,

                'businessDomains' =>
                    $businessDomains,

                'openProjects' =>
                    $openProjects,

                'documentsCount' =>
                    $documentsCount,

                'appointmentsCount' =>
                    $appointmentsCount,
            ]
        );
    }


    public function edit(Client $client)
    {
        return view(
            'clients.edit',
            compact('client')
        );
    }


    public function update(
        Request $request,
        Client $client
    ) {
        $validated = $request->validate([
            'company_name' =>
                'required|max:255',

            'contact_name' =>
                'required|max:255',

            'email' =>
                'required|email|unique:clients,email,'
                . $client->id,

            'phone' =>
                'nullable|max:30',

            'website' =>
                'nullable|max:255',

            'address' =>
                'nullable|max:255',

            'city' =>
                'nullable|max:100',

            'postcode' =>
                'nullable|max:20',

            'country' =>
                'nullable|max:100',

            'status' =>
                'required',

            'notes' =>
                'nullable',
        ]);


        $client->update(
            $validated
        );


        return redirect()
            ->route(
                'clients.index'
            )
            ->with(
                'success',
                'Client updated successfully.'
            );
    }


    public function destroy(
        Client $client
    ) {
        $client->delete();


        return redirect()
            ->route(
                'clients.index'
            )
            ->with(
                'success',
                'Client deleted successfully.'
            );
    }
}