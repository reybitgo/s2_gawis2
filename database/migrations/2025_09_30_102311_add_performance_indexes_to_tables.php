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
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'idx_orders_status');
            $table->index('user_id', 'idx_orders_user_id');
            $table->index('created_at', 'idx_orders_created_at');
            $table->index('order_number', 'idx_orders_order_number');
        });

        // Packages table indexes
        Schema::table('packages', function (Blueprint $table) {
            $table->index('is_active', 'idx_packages_is_active');
            $table->index('slug', 'idx_packages_slug');
        });

        // Transactions table indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('user_id', 'idx_transactions_user_id');
            $table->index('status', 'idx_transactions_status');
            $table->index('type', 'idx_transactions_type');
        });

        // Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_items_order_id');
            $table->index('package_id', 'idx_order_items_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_order_number');
        });

        // Packages table indexes
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex('idx_packages_is_active');
            $table->dropIndex('idx_packages_slug');
        });

        // Transactions table indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_user_id');
            $table->dropIndex('idx_transactions_status');
            $table->dropIndex('idx_transactions_type');
        });

        // Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_order_id');
            $table->dropIndex('idx_order_items_package_id');
        });
    }
};
