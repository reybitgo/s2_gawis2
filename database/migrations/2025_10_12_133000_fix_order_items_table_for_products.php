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
        Schema::table('order_items', function (Blueprint $table) {
            // Make package_id nullable
            if (Schema::hasColumn('order_items', 'package_id')) {
                $table->foreignId('package_id')->nullable()->change();
            }

            // Add product_id if it doesn't exist
            if (!Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('package_id')->constrained('products')->onDelete('restrict');
            }

            // Add item_type if it doesn't exist
            if (!Schema::hasColumn('order_items', 'item_type')) {
                $table->string('item_type')->default('package')->after('id');
            }

            // Add product_snapshot if it doesn't exist
            if (!Schema::hasColumn('order_items', 'product_snapshot')) {
                $table->json('product_snapshot')->nullable()->after('package_snapshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                // Need to drop foreign key before column
                // The default constraint name is order_items_product_id_foreign
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('order_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
            if (Schema::hasColumn('order_items', 'product_snapshot')) {
                $table->dropColumn('product_snapshot');
            }
            if (Schema::hasColumn('order_items', 'package_id')) {
                $table->foreignId('package_id')->nullable(false)->change();
            }
        });
    }
};