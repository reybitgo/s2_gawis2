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
        Schema::table('packages', function (Blueprint $table) {
            $table->string('rank_name', 100)->nullable()->after('name');
            $table->unsignedInteger('rank_order')->default(1)->after('rank_name');
            $table->unsignedInteger('required_direct_sponsors')->default(0)->after('rank_order');
            $table->boolean('is_rankable')->default(true)->after('required_direct_sponsors');
            $table->unsignedBigInteger('next_rank_package_id')->nullable()->after('is_rankable');
            
            $table->foreign('next_rank_package_id')->references('id')->on('packages')->onDelete('set null');
            $table->index('rank_order');
            $table->index('rank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['next_rank_package_id']);
            $table->dropIndex(['rank_order']);
            $table->dropIndex(['rank_name']);
            $table->dropColumn(['rank_name', 'rank_order', 'required_direct_sponsors', 'is_rankable', 'next_rank_package_id']);
        });
    }
};
