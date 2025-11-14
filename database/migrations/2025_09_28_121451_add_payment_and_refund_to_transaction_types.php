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

        if ($driver === 'sqlite') {
            // For SQLite, update the trigger to include payment and refund types
            DB::statement("DROP TRIGGER IF EXISTS transactions_type_check");
            DB::statement("CREATE TRIGGER transactions_type_check
                BEFORE INSERT ON transactions
                FOR EACH ROW
                WHEN NEW.type NOT IN ('deposit', 'withdrawal', 'transfer', 'transfer_out', 'transfer_in', 'transfer_charge', 'withdrawal_fee', 'payment', 'refund')
                BEGIN
                    SELECT RAISE(ABORT, 'Invalid transaction type');
                END");
        } else {
            // For MySQL/PostgreSQL, modify the ENUM to include payment and refund
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer', 'transfer_out', 'transfer_in', 'transfer_charge', 'withdrawal_fee', 'payment', 'refund')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Revert the trigger to exclude payment and refund
            DB::statement("DROP TRIGGER IF EXISTS transactions_type_check");
            DB::statement("CREATE TRIGGER transactions_type_check
                BEFORE INSERT ON transactions
                FOR EACH ROW
                WHEN NEW.type NOT IN ('deposit', 'withdrawal', 'transfer', 'transfer_out', 'transfer_in', 'transfer_charge', 'withdrawal_fee')
                BEGIN
                    SELECT RAISE(ABORT, 'Invalid transaction type');
                END");
        } else {
            // Revert ENUM to exclude payment and refund
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer', 'transfer_out', 'transfer_in', 'transfer_charge', 'withdrawal_fee')");
        }
    }
};
