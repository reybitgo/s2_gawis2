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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // For MySQL, modify the ENUM to include completed status
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'blocked', 'completed') DEFAULT 'pending'");
        } else {
            // For other databases, just ensure the column can accept 'completed'
            // Since we're using string type for flexibility
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Revert to original ENUM values
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'blocked') DEFAULT 'pending'");
        }
    }
};
