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
        Schema::create('monthly_quota_tracker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year')->comment('Year (e.g., 2025)');
            $table->integer('month')->comment('Month (1-12)');
            $table->decimal('total_pv_points', 10, 2)->default(0)->comment('Total PV accumulated this month');
            $table->decimal('required_quota', 10, 2)->default(0)->comment('Required quota based on user\'s package');
            $table->boolean('quota_met')->default(false)->comment('Whether quota is met this month');
            $table->timestamp('last_purchase_at')->nullable()->comment('Last product purchase timestamp');
            $table->timestamps();

            // Composite unique index: one record per user per month
            $table->unique(['user_id', 'year', 'month'], 'user_month_unique');
            
            // Index for quick lookups
            $table->index(['user_id', 'year', 'month']);
            $table->index('quota_met');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_quota_tracker');
    }
};
