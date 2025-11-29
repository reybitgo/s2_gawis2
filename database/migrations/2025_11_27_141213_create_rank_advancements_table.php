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
        Schema::create('rank_advancements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('from_rank', 100)->nullable();
            $table->string('to_rank', 100);
            $table->unsignedBigInteger('from_package_id')->nullable();
            $table->unsignedBigInteger('to_package_id')->nullable();
            $table->enum('advancement_type', ['purchase', 'sponsorship_reward', 'admin_adjustment'])->default('purchase');
            $table->unsignedInteger('required_sponsors')->nullable()->comment('Number of sponsors required for this advancement');
            $table->unsignedInteger('sponsors_count')->nullable()->comment('Actual sponsors count at time of advancement');
            $table->decimal('system_paid_amount', 10, 2)->default(0.00)->comment('Amount paid by system (if reward)');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Order created for the rank advancement');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_package_id')->references('id')->on('packages')->onDelete('set null');
            $table->foreign('to_package_id')->references('id')->on('packages')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            
            $table->index(['user_id', 'created_at']);
            $table->index('advancement_type');
            $table->index('to_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rank_advancements');
    }
};
