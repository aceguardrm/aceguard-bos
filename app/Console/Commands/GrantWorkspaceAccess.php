<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;

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
            $user->forceFill(['is_platform_admin' => !$this->option('revoke')])->save();
        } else {
            $client = Client::find($this->option('workspace'));
            $role = $this->option('role');
            if (!$client || !in_array($role, ['administrator', 'editor', 'viewer'], true)) {
                $this->error('Supply an existing --workspace ID and administrator, editor or viewer role.');
                return self::FAILURE;
            }
            if ($this->option('revoke')) {
                $client->members()->detach($user->id);
            } else {
                $client->members()->syncWithoutDetaching([$user->id => ['role' => $role]]);
            }
        }
        $this->info('Access updated.');
        return self::SUCCESS;
    }
}
