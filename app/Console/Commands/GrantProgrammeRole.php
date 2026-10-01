<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('programme:grant-role {email : Existing user email} {role=programme-lead : Programme role to grant}')]
#[Description('Grant an existing user an authorised programme role')]
class GrantProgrammeRole extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $allowedRoles = ['admin', 'programme-lead', 'matching-team', 'cluster-lead', 'university-lead', 'technical-guild-lead', 'safeguarding', 'reporting-lead'];
        $role = (string) $this->argument('role');

        if (! in_array($role, $allowedRoles, true)) {
            $this->error('Invalid role. Allowed roles: '.implode(', ', $allowedRoles));

            return self::FAILURE;
        }

        $user = User::where('email', (string) $this->argument('email'))->first();
        if (! $user) {
            $this->error('No user exists with that email address. Ask the person to register first.');

            return self::FAILURE;
        }

        $user->update(['roles' => collect($user->roles ?? [])->push($role)->unique()->values()->all()]);
        $this->info("Granted {$role} to {$user->email}.");

        return self::SUCCESS;
    }
}
