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
        Schema::table('transactions', function (Blueprint $table) {
            // Add level column for MLM commission tracking (1-5)
            $table->tinyInteger('level')->nullable()->after('type');

            // Add source_order_id to link transaction to originating order
            $table->foreignId('source_order_id')->nullable()->after('user_id')->constrained('orders')->onDelete('set null');

            // Add source_type to categorize transaction origin
            $table->enum('source_type', ['mlm', 'deposit', 'transfer', 'purchase', 'withdrawal', 'refund'])->default('deposit')->after('type');

            // Add indexes for performance
            $table->index('source_order_id');
            $table->index('source_type');
            $table->index(['type', 'source_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['transactions_source_order_id_index']);
            $table->dropIndex(['transactions_source_type_index']);
            $table->dropIndex(['transactions_type_source_type_index']);

            // Drop foreign key
            $table->dropForeign(['source_order_id']);

            // Drop columns
            $table->dropColumn(['level', 'source_order_id', 'source_type']);
        });
    }
};
