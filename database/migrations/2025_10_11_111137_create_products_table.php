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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('points_awarded')->default(0);
            $table->integer('quantity_available')->nullable(); // null = unlimited
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('meta_data')->nullable();

            // Unilevel specific fields (optional, for summary display)
            $table->decimal('total_unilevel_bonus', 10, 2)->default(0); // Sum of all level bonuses (auto-calculated)

            // SKU and categorization
            $table->string('sku')->unique()->nullable();
            $table->string('category')->nullable();
            $table->integer('weight_grams')->nullable(); // For shipping calculation

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
