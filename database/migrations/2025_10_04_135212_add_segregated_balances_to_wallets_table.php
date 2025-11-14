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
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('mlm_balance', 10, 2)->default(0.00)->after('balance');
            $table->decimal('purchase_balance', 10, 2)->default(0.00)->after('mlm_balance');
        });

        // Migrate existing balance to purchase_balance
        DB::statement('UPDATE wallets SET purchase_balance = balance, balance = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate balances back before dropping columns
        DB::statement('UPDATE wallets SET balance = mlm_balance + purchase_balance');

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['mlm_balance', 'purchase_balance']);
        });
    }
};
