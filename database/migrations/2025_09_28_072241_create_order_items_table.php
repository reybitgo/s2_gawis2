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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('restrict'); // Don't allow package deletion if used in orders

            // Item details at time of purchase
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Price per item at time of purchase
            $table->decimal('total_price', 10, 2); // Total price for this line item
            $table->integer('points_awarded_per_item')->default(0); // Points per item at time of purchase
            $table->integer('total_points_awarded')->default(0); // Total points for this line item

            // Package snapshot to preserve package details even if package is modified later
            $table->json('package_snapshot')->nullable(); // Store package name, description, image, etc. at time of purchase

            $table->timestamps();

            // Indexes for performance
            $table->index(['order_id']);
            $table->index(['package_id']);
            $table->index(['order_id', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};