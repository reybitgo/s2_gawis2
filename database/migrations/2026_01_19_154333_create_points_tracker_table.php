<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_tracker', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_item_id');
            $table->decimal('ppv', 10, 2)->default(0);
            $table->decimal('gpv', 10, 2)->default(0);
            $table->timestamp('earned_at')->useCurrent();
            $table->unsignedBigInteger('awarded_to_user_id')->nullable()->comment('User who received credit for this');
            $table->string('point_type', 50)->default('product_purchase')->comment('product_purchase, repeat_purchase, etc.');
            $table->string('rank_at_time', 100)->nullable()->comment('User rank when points earned');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('awarded_to_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['user_id', 'earned_at']);
            $table->index('point_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_tracker');
    }
};
