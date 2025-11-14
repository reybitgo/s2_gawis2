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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->enum('type', ['security', 'transaction', 'mlm_commission', 'unilevel_bonus', 'wallet', 'system', 'order'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->enum('type', ['security', 'transaction', 'mlm_commission', 'wallet', 'system', 'order'])->change();
        });
    }
};
