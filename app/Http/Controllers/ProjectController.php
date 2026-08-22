<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with('client')
            ->latest();

        $selectedClient = null;

        if ($request->filled('client')) {
            $selectedClient = Client::find(
                $request->integer('client')
            );

            if ($selectedClient) {
                $query->where(
                    'client_id',
                    $selectedClient->id
                );
            }
        }

        $projects = $query
            ->paginate(12)
            ->withQueryString();

        return view(
            'projects.index',
            compact(
                'projects',
                'selectedClient'
            )
        );
    }


    /**
     * Show the form for creating a new project.
     */
    public function create(Request $request)
    {
        $clients = Client::orderBy('company_name')
            ->get();

        $selectedClient = null;

        if ($request->filled('client')) {
            $selectedClient = Client::find(
                $request->integer('client')
            );
        }

        return view(
            'projects.create',
            compact(
                'clients',
                'selectedClient'
            )
        );
    }


    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' =>
                'required|exists:clients,id',

            'name' =>
                'required|string|max:255',

            'description' =>
                'nullable|string',

            'owner_name' =>
                'nullable|string|max:255',

            'owner_email' =>
                'nullable|email|max:255',

            'status' =>
                'required|in:planned,in_progress,on_hold,completed,cancelled',

            'priority' =>
                'required|in:low,medium,high,critical',

            'start_date' =>
                'nullable|date',

            'due_date' =>
                'nullable|date|after_or_equal:start_date',

            'progress' =>
                'required|integer|min:0|max:100',

            'source' =>
                'nullable|string|max:255',

            'source_reference' =>
                'nullable|string|max:255',

            'notes' =>
                'nullable|string',
        ]);

        $project = Project::create(
            $validated
        );

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Project created successfully.'
            );
    }


    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $project->load([
            'client',
            'tasks',
        ]);

        return view(
            'projects.show',
            compact('project')
        );
    }


    /**
     * Show the form for editing the project.
     */
    public function edit(Project $project)
    {
        $clients = Client::orderBy('company_name')
            ->get();

        return view(
            'projects.edit',
            compact(
                'project',
                'clients'
            )
        );
    }


    /**
     * Update the specified project.
     */
    public function update(
        Request $request,
        Project $project
    ) {
        $validated = $request->validate([
            'client_id' =>
                'required|exists:clients,id',

            'name' =>
                'required|string|max:255',

            'description' =>
                'nullable|string',

            'owner_name' =>
                'nullable|string|max:255',

            'owner_email' =>
                'nullable|email|max:255',

            'status' =>
                'required|in:planned,in_progress,on_hold,completed,cancelled',

            'priority' =>
                'required|in:low,medium,high,critical',

            'start_date' =>
                'nullable|date',

            'due_date' =>
                'nullable|date|after_or_equal:start_date',

            'progress' =>
                'required|integer|min:0|max:100',

            'source' =>
                'nullable|string|max:255',

            'source_reference' =>
                'nullable|string|max:255',

            'notes' =>
                'nullable|string',
        ]);

        $project->update(
            $validated
        );

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Project updated successfully.'
            );
    }


    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with(
                'success',
                'Project deleted successfully.'
            );
    }
}