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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('network_status', ['inactive', 'active', 'suspended'])->default('inactive')->after('referral_code');
            $table->timestamp('network_activated_at')->nullable()->after('network_status');
            $table->timestamp('last_product_purchase_at')->nullable()->after('network_activated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['network_status', 'network_activated_at', 'last_product_purchase_at']);
        });
    }
};