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
        Schema::create('direct_sponsors_tracker', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sponsored_user_id');
            $table->timestamp('sponsored_at')->useCurrent();
            $table->string('sponsored_user_rank_at_time', 100)->nullable();
            $table->unsignedBigInteger('sponsored_user_package_id')->nullable();
            $table->string('counted_for_rank', 100)->nullable()->comment('Which rank this sponsorship counted towards');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sponsored_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sponsored_user_package_id')->references('id')->on('packages')->onDelete('set null');
            
            $table->unique(['user_id', 'sponsored_user_id'], 'unique_sponsorship');
            $table->index(['user_id', 'sponsored_user_rank_at_time'], 'idx_user_sponsor_rank');
            $table->index(['user_id', 'counted_for_rank'], 'idx_user_counted_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_sponsors_tracker');
    }
};
