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
        Schema::table('users', function (Blueprint $table) {
            $table->string('current_rank', 100)->nullable()->after('network_activated_at');
            $table->unsignedBigInteger('rank_package_id')->nullable()->after('current_rank');
            $table->timestamp('rank_updated_at')->nullable()->after('rank_package_id');
            
            $table->foreign('rank_package_id')->references('id')->on('packages')->onDelete('set null');
            $table->index('current_rank');
            $table->index('rank_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rank_package_id']);
            $table->dropIndex(['current_rank']);
            $table->dropIndex(['rank_package_id']);
            $table->dropColumn(['current_rank', 'rank_package_id', 'rank_updated_at']);
        });
    }
};
