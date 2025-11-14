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
        // Add 'unilevel_bonus' to the transaction type enum
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN type ENUM(
                'deposit',
                'withdrawal',
                'transfer',
                'transfer_out',
                'transfer_in',
                'transfer_charge',
                'withdrawal_fee',
                'payment',
                'refund',
                'mlm_commission',
                'balance_conversion',
                'unilevel_bonus'
            ) NOT NULL
        ");

        // Add 'unilevel' to the transaction source_type enum
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN source_type ENUM(
                'mlm',
                'unilevel'
            ) NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'unilevel_bonus' from the transaction type enum
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN type ENUM(
                'deposit',
                'withdrawal',
                'transfer',
                'transfer_out',
                'transfer_in',
                'transfer_charge',
                'withdrawal_fee',
                'payment',
                'refund',
                'mlm_commission',
                'balance_conversion'
            ) NOT NULL
        ");

        // Remove 'unilevel' from the transaction source_type enum
        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN source_type ENUM(
                'mlm'
            ) NULL
        ");
    }
};
