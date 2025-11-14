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
        // Since SQLite doesn't support altering ENUM columns, we need to recreate the table
        // For testing purposes, we'll handle this differently for SQLite vs other databases
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite, we'll drop and recreate the column as TEXT with a check constraint
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });

            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type')->after('user_id');
            });

            // Add check constraint for allowed values
            DB::statement("CREATE TRIGGER transactions_type_check
                BEFORE INSERT ON transactions
                FOR EACH ROW
                WHEN NEW.type NOT IN ('deposit', 'withdrawal', 'transfer', 'transfer_out', 'transfer_in', 'transfer_charge', 'withdrawal_fee')
                BEGIN
                    SELECT RAISE(ABORT, 'Invalid transaction type');
                END");
        } else {
            // For MySQL/PostgreSQL, use ALTER TABLE to modify the ENUM
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer', 'transfer_out', 'transfer_in', 'transfer_charge', 'withdrawal_fee')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Drop the trigger and recreate the column as original
            DB::statement("DROP TRIGGER IF EXISTS transactions_type_check");

            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });

            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type')->after('user_id');
            });
        } else {
            // Revert to original ENUM values
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer')");
        }
    }
};
