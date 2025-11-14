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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['restock', 'sale', 'reservation', 'release', 'adjustment', 'return'])->index();
            $table->integer('quantity_before')->unsigned();
            $table->integer('quantity_after')->unsigned();
            $table->integer('quantity_change');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference')->nullable()->comment('Order number, reservation ID, etc.');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes for reporting
            $table->index(['package_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
