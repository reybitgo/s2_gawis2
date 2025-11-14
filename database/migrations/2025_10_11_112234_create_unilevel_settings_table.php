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
        Schema::create('unilevel_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->tinyInteger('level'); // 1-5
            $table->decimal('bonus_amount', 10, 2); // Fixed amount per level (e.g., ₱20, ₱10, etc.)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'level']);
            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unilevel_settings');
    }
};
