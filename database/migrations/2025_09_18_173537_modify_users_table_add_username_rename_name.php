<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add username column without unique constraint
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('id');
        });

        // Populate usernames for existing users
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            // Generate username from email (before @ symbol)
            $baseUsername = strtolower(explode('@', $user->email)[0]);
            // Remove any non-alphanumeric characters except underscores
            $baseUsername = preg_replace('/[^a-z0-9_]/', '', $baseUsername);

            // Ensure username is unique
            $username = $baseUsername;
            $counter = 1;
            while (\App\Models\User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user->update(['username' => $username]);
        }

        // Now add unique constraint and make it non-nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });

        // Rename name column to fullname
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'fullname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename fullname back to name
            $table->renameColumn('fullname', 'name');

            // Drop username column
            $table->dropColumn('username');
        });
    }
};
