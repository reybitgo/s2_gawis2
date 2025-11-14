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
            // Drop the old balance columns that are no longer needed
            // The system now uses mlm_balance and purchase_balance
            $table->dropColumn(['balance', 'reserved_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Restore the old columns if needed
            $table->decimal('balance', 10, 2)->default(0)->after('user_id');
            $table->decimal('reserved_balance', 10, 2)->default(0)->after('balance');
        });
    }
};
