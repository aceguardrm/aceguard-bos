<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkspaceMemberController extends Controller
{
    private function authorizeAdministrator(Request $request, Client $client): void
    {
        abort_unless($request->user()->canAccessWorkspace($client), 404);
        abort_unless($request->user()->canAccessWorkspace($client, 'admin'), 403);
    }

    public function index(Request $request, Client $client)
    {
        $this->authorizeAdministrator($request, $client);

        return response()->view('clients.members', [
            'client' => $client,
            'members' => $client->members()->orderBy('name')->get(),
            'invitations' => \App\Models\WorkspaceInvitation::where('client_id', $client->id)
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->latest()->get(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, Client $client, User $member)
    {
        $this->authorizeAdministrator($request, $client);

        $validated = $request->validate([
            'role' => ['required', Rule::in(['viewer', 'editor', 'administrator'])],
        ]);

        DB::transaction(function () use ($request, $client, $member, $validated) {
            // Serialize membership changes within this workspace.
            $locked = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
            $this->authorizeAdministrator($request, $locked);
            $membership = $locked->members()->whereKey($member->id)->first();
            abort_unless($membership, 404);

            $this->protectLastAdministrator(
                $locked,
                $membership,
                $validated['role'] !== 'administrator'
            );

            $locked->members()->updateExistingPivot($member->id, [
                'role' => $validated['role'],
            ]);
        });

        if (!$request->user()->canAccessWorkspace($client, 'admin')) {
            return redirect()->route('clients.show', $client)
                ->with('success', 'Member role updated.');
        }

        return redirect()->route('clients.members.index', $client)
            ->with('success', 'Member role updated.');
    }

    public function destroy(Request $request, Client $client, User $member)
    {
        $this->authorizeAdministrator($request, $client);

        DB::transaction(function () use ($request, $client, $member) {
            $locked = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
            $this->authorizeAdministrator($request, $locked);
            $membership = $locked->members()->whereKey($member->id)->first();
            abort_unless($membership, 404);

            $this->protectLastAdministrator($locked, $membership, true);
            $locked->members()->detach($member->id);
        });

        if (!$request->user()->canAccessWorkspace($client, 'admin')) {
            return redirect()->route('clients.index')
                ->with('success', 'Workspace access removed.');
        }

        return redirect()->route('clients.members.index', $client)
            ->with('success', 'Member access removed.');
    }

    private function protectLastAdministrator(
        Client $client,
        User $member,
        bool $removingAdministrator
    ): void {
        if ($removingAdministrator
            && $member->pivot->role === 'administrator'
            && $client->members()->wherePivot('role', 'administrator')->count() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'Assign another workspace administrator first.',
            ]);
        }
    }
}
