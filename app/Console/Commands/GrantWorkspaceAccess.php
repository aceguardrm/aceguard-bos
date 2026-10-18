<?php

namespace App\Console\Commands;

use App\Models\AccessAuditEvent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrantWorkspaceAccess extends Command
{
    protected $signature = 'bos:access {email} {--workspace=} {--role=viewer} {--platform-admin} {--revoke}';

    protected $description = 'Explicitly grant or revoke an existing user’s workspace or platform access';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('User not found. No changes made.');
            return self::FAILURE;
        }

        if ($this->option('platform-admin')) {
            if ($this->option('workspace')) {
                $this->error('Choose platform access or workspace access, not both.');
                return self::FAILURE;
            }

            DB::transaction(function () use ($user) {
                $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $previous = (bool) $locked->is_platform_admin;
                $next = !$this->option('revoke');

                if ($previous === $next) {
                    return;
                }

                $locked->forceFill(['is_platform_admin' => $next])->save();

                AccessAuditEvent::create([
                    'subject_user_id' => $locked->id,
                    'source' => 'cli',
                    'action' => $next
                        ? 'platform.admin_granted'
                        : 'platform.admin_revoked',
                    'previous_role' => $previous ? 'platform_admin' : null,
                    'new_role' => $next ? 'platform_admin' : null,
                    'created_at' => now(),
                ]);
            });
        } else {
            $client = Client::find($this->option('workspace'));
            $role = $this->option('role');

            if (!$client || !in_array($role, ['administrator', 'editor', 'viewer'], true)) {
                $this->error('Supply an existing --workspace ID and administrator, editor or viewer role.');
                return self::FAILURE;
            }

            DB::transaction(function () use ($client, $user, $role) {
                $locked = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
                $membership = $locked->members()->whereKey($user->id)->first();
                $previous = $membership?->pivot->role;
                $next = $this->option('revoke') ? null : $role;

                if ($previous === $next) {
                    return;
                }

                if ($next === null) {
                    $locked->members()->detach($user->id);
                    $action = 'workspace.member_removed';
                } else {
                    $locked->members()->syncWithoutDetaching([
                        $user->id => ['role' => $next],
                    ]);
                    $action = $previous === null
                        ? 'workspace.member_added'
                        : 'workspace.role_changed';
                }

                AccessAuditEvent::create([
                    'client_id' => $locked->id,
                    'subject_user_id' => $user->id,
                    'source' => 'cli',
                    'action' => $action,
                    'previous_role' => $previous,
                    'new_role' => $next,
                    'created_at' => now(),
                ]);
            });
        }

        $this->info('Access updated.');
        return self::SUCCESS;
    }
}
