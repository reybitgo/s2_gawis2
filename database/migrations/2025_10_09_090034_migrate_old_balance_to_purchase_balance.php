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
        // Migrate existing balances from 'balance' column to 'purchase_balance'
        // This is a one-time migration to fix wallets that were created before
        // the MLM balance system was implemented

        $affectedRows = DB::update('
            UPDATE wallets
            SET purchase_balance = purchase_balance + balance,
                balance = 0
            WHERE balance > 0
        ');

        \Log::info('Migrated old wallet balances to purchase_balance', [
            'affected_rows' => $affectedRows
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the migration by moving purchase_balance back to balance
        // Only do this if mlm_balance is 0 (to avoid data loss)

        DB::statement('
            UPDATE wallets
            SET balance = purchase_balance,
                purchase_balance = 0
            WHERE mlm_balance = 0 AND purchase_balance > 0
        ');
    }
};
