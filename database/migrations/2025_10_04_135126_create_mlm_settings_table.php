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
        Schema::create('mlm_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->tinyInteger('level')->unsigned(); // 1 to 5
            $table->decimal('commission_amount', 10, 2); // 200 for L1, 50 for L2-5
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: one setting per package per level
            $table->unique(['package_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mlm_settings');
    }
};
