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
            $table->decimal('monthly_quota_points', 10, 2)->default(0)->after('max_mlm_levels')
                ->comment('Required monthly PV points to earn Unilevel bonuses');
            $table->boolean('enforce_monthly_quota')->default(false)->after('monthly_quota_points')
                ->comment('Enable/disable monthly quota requirement for this package');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['monthly_quota_points', 'enforce_monthly_quota']);
        });
    }
};
