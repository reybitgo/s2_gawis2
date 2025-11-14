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
            $table->boolean('is_mlm_package')->default(false)->after('meta_data');
            $table->tinyInteger('max_mlm_levels')->unsigned()->default(5)->after('is_mlm_package');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['is_mlm_package', 'max_mlm_levels']);
        });
    }
};
