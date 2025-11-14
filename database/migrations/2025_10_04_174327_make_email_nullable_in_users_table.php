<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unique constraint on email if it exists
        try {
            DB::statement('ALTER TABLE users DROP INDEX users_email_unique');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Note: MySQL does not support partial indexes with WHERE clause
        // We rely on application-level validation to ensure email uniqueness when not null
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        // Re-create standard unique constraint
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        } catch (\Exception $e) {
            // Index might already exist
        }
    }
};
