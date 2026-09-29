<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;

/** Runs after route binding. Collection queries must also use visibleTo(). */
class AuthorizeWorkspace
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user, 403);
        $name = $request->route()->getName();
        $client = $request->route('client');
        $project = $request->route('project');
        if ($project instanceof Project) {
            $client = $project->client;
            abort_unless($client, 404);
        }
        if (in_array($name, ['clients.create', 'clients.store', 'clients.destroy'], true)) {
            abort_unless($user->is_platform_admin, 403);
        }
        if ($client instanceof Client) {
            abort_unless($user->canAccessWorkspace($client), 404);
            $write = !$request->isMethodSafe() || str_ends_with($name, '.edit');
            if ($write) {
                $level = str_starts_with($name, 'clients.') ? 'admin' : 'edit';
                abort_unless($user->canAccessWorkspace($client, $level), 403);
            }
        }
        // Project creation/reassignment must authorize the submitted destination too.
        if (in_array($name, ['projects.store', 'projects.update'], true)) {
            $id = $request->input('client_id');
            abort_unless(is_scalar($id) && ctype_digit((string) $id), 422);
            $target = Client::visibleTo($user)->findOrFail($id);
            abort_unless($user->canAccessWorkspace($target, 'edit'), 403);
        }
        if ($name === 'projects.create') {
            abort_unless($user->is_platform_admin || $user->workspaces()
                ->wherePivotIn('role', ['administrator', 'editor'])->exists(), 403);
        }
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
