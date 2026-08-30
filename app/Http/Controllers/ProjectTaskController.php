<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    /**
     * Store a new project task or milestone.
     */
    public function store(
        Request $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validate([
            'title' =>
                'required|string|max:255',

            'description' =>
                'nullable|string',

            'is_milestone' =>
                'nullable|boolean',

            'owner_name' =>
                'nullable|string|max:255',

            'owner_email' =>
                'nullable|email|max:255',

            'status' =>
                'required|in:pending,in_progress,blocked,completed',

            'priority' =>
                'required|in:low,medium,high,critical',

            'due_date' =>
                'nullable|date',

            'notes' =>
                'nullable|string',
        ]);

        $validated['is_milestone'] =
            $request->boolean('is_milestone');

        $validated['sort_order'] =
            ($project->tasks()->max('sort_order') ?? 0) + 1;

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $project
            ->tasks()
            ->create($validated);

        $project->syncProgressFromTasks();

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Project task created successfully.'
            );
    }


    /**
     * Update an existing project task.
     */
    public function update(
        Request $request,
        Project $project,
        ProjectTask $projectTask
    ): RedirectResponse {
        $this->ensureTaskBelongsToProject(
            $project,
            $projectTask
        );

        $validated = $request->validate([
            'title' =>
                'required|string|max:255',

            'description' =>
                'nullable|string',

            'is_milestone' =>
                'nullable|boolean',

            'owner_name' =>
                'nullable|string|max:255',

            'owner_email' =>
                'nullable|email|max:255',

            'status' =>
                'required|in:pending,in_progress,blocked,completed',

            'priority' =>
                'required|in:low,medium,high,critical',

            'due_date' =>
                'nullable|date',

            'notes' =>
                'nullable|string',
        ]);

        $validated['is_milestone'] =
            $request->boolean('is_milestone');

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] =
                $projectTask->completed_at ?? now();
        } else {
            $validated['completed_at'] = null;
        }

        $projectTask->update($validated);

        $project->syncProgressFromTasks();

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Project task updated successfully.'
            );
    }


    /**
     * Toggle task completion.
     */
    public function toggle(
        Project $project,
        ProjectTask $projectTask
    ): RedirectResponse {
        $this->ensureTaskBelongsToProject(
            $project,
            $projectTask
        );

        if ($projectTask->status === 'completed') {
            $projectTask->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
        } else {
            $projectTask->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $project->syncProgressFromTasks();

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Task status updated.'
            );
    }


    /**
     * Delete a task.
     */
    public function destroy(
        Project $project,
        ProjectTask $projectTask
    ): RedirectResponse {
        $this->ensureTaskBelongsToProject(
            $project,
            $projectTask
        );

        $projectTask->delete();

        $project->syncProgressFromTasks();

        return redirect()
            ->route(
                'projects.show',
                $project
            )
            ->with(
                'success',
                'Project task deleted successfully.'
            );
    }


    /**
     * Confirm the nested task belongs to this project.
     */
    private function ensureTaskBelongsToProject(
        Project $project,
        ProjectTask $projectTask
    ): void {
        abort_unless(
            (int) $projectTask->project_id
                === (int) $project->id,
            404
        );
    }
}