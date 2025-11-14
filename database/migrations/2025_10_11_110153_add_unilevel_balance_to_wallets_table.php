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
            // Add unilevel_balance column after mlm_balance
            $table->decimal('unilevel_balance', 10, 2)->default(0.00)->after('mlm_balance');

            // Add index for performance
            $table->index('unilevel_balance');
            $table->index('mlm_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['unilevel_balance']);
            $table->dropIndex(['mlm_balance']);
            $table->dropColumn('unilevel_balance');
        });
    }
};
