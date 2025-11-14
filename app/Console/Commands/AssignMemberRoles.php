<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignMemberRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-member-roles {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign member role to users who do not have any roles assigned';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Find users without any roles
        $usersWithoutRoles = User::doesntHave('roles')->get();
        $memberRole = Role::where('name', 'member')->first();

        if (!$memberRole) {
            $this->error('Member role not found! Please run the role seeder first.');
            return Command::FAILURE;
        }

        if ($usersWithoutRoles->isEmpty()) {
            $this->info('All users already have roles assigned.');
            return Command::SUCCESS;
        }

        $this->info("Found {$usersWithoutRoles->count()} users without roles:");

        foreach ($usersWithoutRoles as $user) {
            // Don't assign member role to admin users (identified by email)
            if ($user->email === 'admin@ewallet.com') {
                $this->warn("Skipping admin user: {$user->email}");
                continue;
            }

            if ($dryRun) {
                $this->line("Would assign member role to: {$user->email} ({$user->username})");
            } else {
                $user->assignRole('member');
                $this->info("Assigned member role to: {$user->email} ({$user->username})");
            }
        }

        $affectedUsers = $usersWithoutRoles->where('email', '!=', 'admin@ewallet.com')->count();

        if ($dryRun) {
            $this->info("Dry run complete. Would affect {$affectedUsers} users.");
            $this->comment('Run without --dry-run to make actual changes.');
        } else {
            $this->info("Successfully assigned member role to {$affectedUsers} users.");
        }

        return Command::SUCCESS;
    }
}