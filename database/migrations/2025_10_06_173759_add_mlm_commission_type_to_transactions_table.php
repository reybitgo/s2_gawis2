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
        // Add mlm_commission to the transaction type enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
            'deposit',
            'withdrawal',
            'transfer',
            'transfer_out',
            'transfer_in',
            'transfer_charge',
            'withdrawal_fee',
            'payment',
            'refund',
            'mlm_commission'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove mlm_commission from the transaction type enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM(
            'deposit',
            'withdrawal',
            'transfer',
            'transfer_out',
            'transfer_in',
            'transfer_charge',
            'withdrawal_fee',
            'payment',
            'refund'
        ) NULL");
    }
};
