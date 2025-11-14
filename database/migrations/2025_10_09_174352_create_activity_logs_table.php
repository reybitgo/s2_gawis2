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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Log metadata
            $table->enum('level', ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'])->default('INFO');
            $table->enum('type', ['security', 'transaction', 'mlm_commission', 'wallet', 'system', 'order'])->index();
            $table->string('event')->index(); // e.g., 'commission_earned', 'deposit_approved', 'withdrawal_requested'
            $table->text('message');

            // User and request information
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Related entities (polymorphic-like approach)
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedBigInteger('related_user_id')->nullable(); // For MLM - who triggered the commission

            // Additional data stored as JSON
            $table->json('metadata')->nullable(); // Store commission details, amounts, levels, etc.

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['level', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
