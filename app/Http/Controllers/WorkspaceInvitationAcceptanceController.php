<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class WorkspaceInvitationAcceptanceController extends Controller
{
    private function invitation(string $token): WorkspaceInvitation
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token), 404);

        $invitation = WorkspaceInvitation::where(
            'token_hash', hash('sha256', $token)
        )->firstOrFail();

        abort_unless($invitation->isUsable(), 410);
        return $invitation;
    }

    public function show(Request $request, string $token)
    {
        $invitation = $this->invitation($token);
        $existing = User::whereRaw('LOWER(email) = ?', [$invitation->email])->exists();

        if ($request->user()) {
            abort_unless(
                strtolower($request->user()->email) === $invitation->email,
                403
            );
        } elseif ($existing) {
            $request->session()->put(
                'url.intended', route('workspace-invitations.show', ['token' => $token])
            );
        }

        return response()->view('invitations.accept', [
            'invitation' => $invitation,
            'token' => $token,
            'existingAccount' => $existing,
        ])->withHeaders([
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    public function store(Request $request, string $token)
    {
        $initial = $this->invitation($token);

        if (!$request->user()) {
            if (User::whereRaw('LOWER(email) = ?', [$initial->email])->exists()) {
                $request->session()->put(
                    'url.intended', route('workspace-invitations.show', ['token' => $token])
                );
                return redirect()->route('login');
            }

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::min(12)],
            ]);
        }

        DB::transaction(function () use ($request, $initial, $token) {
            // Same lock order as invitation creation and cancellation.
            $client = Client::whereKey($initial->client_id)
                ->lockForUpdate()->firstOrFail();
            $invitation = WorkspaceInvitation::whereKey($initial->id)
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()->firstOrFail();

            abort_unless($invitation->isUsable(), 410);
            abort_unless(
                in_array($invitation->role, ['viewer', 'editor', 'administrator'], true),
                410
            );

            // Invitations stop working if their sender loses administrator access.
            $inviter = User::find($invitation->invited_by);
            abort_unless($inviter && $inviter->canAccessWorkspace($client, 'admin'), 410);

            $user = $request->user();

            if ($user) {
                abort_unless(strtolower($user->email) === $invitation->email, 403);
            } else {
                if (User::whereRaw('LOWER(email) = ?', [$invitation->email])->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'An account already exists. Sign in before accepting.',
                    ]);
                }

                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $invitation->email,
                    'password' => $request->input('password'),
                ]);
            }

            // Preserve any membership assigned after the invitation was issued.
            $membership = $client->members()->whereKey($user->id)->first();
            $previousRole = $membership?->pivot->role;

            if (!$membership) {
                $client->members()->attach($user->id, ['role' => $invitation->role]);

                \App\Models\AccessAuditEvent::create([
                    'client_id' => $client->id,
                    'actor_id' => $request->user()?->id,
                    'subject_user_id' => $user->id,
                    'invitation_id' => $invitation->id,
                    'source' => 'web',
                    'action' => 'workspace.member_added',
                    'new_role' => $invitation->role,
                    'created_at' => now(),
                ]);
            }

            $invitation->update(['accepted_at' => now()]);

            \App\Models\AccessAuditEvent::create([
                'client_id' => $client->id,
                'actor_id' => $request->user()?->id,
                'subject_user_id' => $user->id,
                'invitation_id' => $invitation->id,
                'source' => 'web',
                'action' => 'invitation.accepted',
                'previous_role' => $previousRole,
                'new_role' => $previousRole ?? $invitation->role,
                'created_at' => now(),
            ]);
        });

        if (!$request->user()) {
            return redirect()->route('login')
                ->with('status', 'Account created. Sign in to access your workspace.');
        }

        return redirect()->route('clients.show', $initial->client_id)
            ->with('success', 'Invitation accepted.');
    }
}
