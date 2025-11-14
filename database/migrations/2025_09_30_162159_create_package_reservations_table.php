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
        Schema::create('package_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->unsigned();
            $table->string('session_id')->index();
            $table->timestamp('expires_at')->index();
            $table->enum('status', ['active', 'completed', 'expired', 'cancelled'])->default('active');
            $table->string('reference')->nullable()->comment('Order number if completed');
            $table->timestamps();

            // Index for cleanup queries
            $table->index(['expires_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_reservations');
    }
};
