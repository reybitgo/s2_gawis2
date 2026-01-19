<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedInteger('required_sponsors_ppv_gpv')->default(4)->after('required_direct_sponsors')->comment('Minimum same-rank sponsors required for PPV/GPV advancement');
            $table->decimal('ppv_required', 10, 2)->default(0)->after('required_sponsors_ppv_gpv');
            $table->decimal('gpv_required', 10, 2)->default(0)->after('ppv_required');
            $table->boolean('rank_pv_enabled')->default(true)->after('gpv_required')->comment('Enable PV-based rank advancement for this rank');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'required_sponsors_ppv_gpv',
                'ppv_required',
                'gpv_required',
                'rank_pv_enabled',
            ]);
        });
    }
};
