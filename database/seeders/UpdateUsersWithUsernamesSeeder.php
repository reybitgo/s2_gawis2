<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateUsersWithUsernamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and add usernames if they don't have one
        $users = \App\Models\User::whereNull('username')->orWhere('username', '')->get();

        foreach ($users as $user) {
            // Generate username from email (before @ symbol)
            $baseUsername = strtolower(explode('@', $user->email)[0]);

            // Remove any non-alphanumeric characters except underscores
            $baseUsername = preg_replace('/[^a-z0-9_]/', '', $baseUsername);

            // Ensure username is unique
            $username = $baseUsername;
            $counter = 1;

            while (\App\Models\User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Update the user with the new username
            $user->update(['username' => $username]);

            $this->command->info("Updated user {$user->email} with username: {$username}");
        }

        $this->command->info("Updated " . count($users) . " users with usernames.");
    }
}
