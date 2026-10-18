<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WorkspaceInvitation;
use App\Notifications\WorkspaceInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkspaceInvitationController extends Controller
{
    private function authorizeAdministrator(Request $request, Client $client): void
    {
        abort_unless($request->user()->canAccessWorkspace($client), 404);
        abort_unless($request->user()->canAccessWorkspace($client, 'admin'), 403);
    }

    public function store(Request $request, Client $client)
    {
        $this->authorizeAdministrator($request, $client);

        $email = $request->input('email');
        if (is_string($email)) {
            $request->merge(['email' => Str::lower(trim($email))]);
        }
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', Rule::in(['viewer', 'editor', 'administrator'])],
        ]);

        $token = bin2hex(random_bytes(32));

        $invitation = DB::transaction(function () use ($request, $client, $validated, $token) {
            $locked = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
            $this->authorizeAdministrator($request, $locked);

            if ($locked->members()
                ->whereRaw('LOWER(users.email) = ?', [$validated['email']])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This person is already a workspace member.',
                ]);
            }

            $previousInvitations = WorkspaceInvitation::where('client_id', $locked->id)
                ->where('email', $validated['email'])
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->lockForUpdate()->get();

            foreach ($previousInvitations as $previous) {
                $previous->update(['revoked_at' => now()]);
                \App\Models\AccessAuditEvent::create([
                    'client_id' => $locked->id,
                    'actor_id' => $request->user()->id,
                    'invitation_id' => $previous->id,
                    'source' => 'web',
                    'action' => 'invitation.replaced',
                    'previous_role' => $previous->role,
                    'created_at' => now(),
                ]);
            }

            $created = WorkspaceInvitation::create([
                'client_id' => $locked->id,
                'invited_by' => $request->user()->id,
                'email' => $validated['email'],
                'role' => $validated['role'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);

            \App\Models\AccessAuditEvent::create([
                'client_id' => $locked->id,
                'actor_id' => $request->user()->id,
                'invitation_id' => $created->id,
                'source' => 'web',
                'action' => 'invitation.created',
                'new_role' => $created->role,
                'created_at' => now(),
            ]);

            return $created;
        });

        try {
            Notification::route('mail', $invitation->email)->notify(
                new WorkspaceInvitationNotification(
                    $client->company_name,
                    $invitation->role,
                    rtrim(config('app.url'), '/').'/workspace-invitations/'.$token
                )
            );
        } catch (\Throwable $error) {
            DB::transaction(function () use ($request, $client, $invitation) {
                Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
                $pending = WorkspaceInvitation::whereKey($invitation->id)
                    ->lockForUpdate()->firstOrFail();

                if ($pending->accepted_at === null && $pending->revoked_at === null) {
                    $pending->update(['revoked_at' => now()]);
                    \App\Models\AccessAuditEvent::create([
                        'client_id' => $client->id,
                        'actor_id' => $request->user()->id,
                        'invitation_id' => $pending->id,
                        'source' => 'web',
                        'action' => 'invitation.delivery_failed',
                        'previous_role' => $pending->role,
                        'created_at' => now(),
                    ]);
                }
            });
            // Do not log the invitation URL or mailbox credentials.
            throw ValidationException::withMessages([
                'email' => 'The invitation could not be sent. Please try again.',
            ]);
        }

        return redirect()->route('clients.members.index', $client)
            ->with('success', 'Invitation submitted for email delivery.');
    }

    public function destroy(
        Request $request,
        Client $client,
        WorkspaceInvitation $invitation
    ) {
        $this->authorizeAdministrator($request, $client);
        abort_unless($invitation->client_id === $client->id, 404);

        DB::transaction(function () use ($request, $client, $invitation) {
            $locked = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
            $this->authorizeAdministrator($request, $locked);

            $pending = WorkspaceInvitation::whereKey($invitation->id)
                ->where('client_id', $locked->id)
                ->lockForUpdate()->firstOrFail();

            if ($pending->accepted_at === null && $pending->revoked_at === null) {
                $pending->update(['revoked_at' => now()]);
                \App\Models\AccessAuditEvent::create([
                    'client_id' => $locked->id,
                    'actor_id' => $request->user()->id,
                    'invitation_id' => $pending->id,
                    'source' => 'web',
                    'action' => 'invitation.cancelled',
                    'previous_role' => $pending->role,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('clients.members.index', $client)
            ->with('success', 'Invitation cancelled.');
    }
}
