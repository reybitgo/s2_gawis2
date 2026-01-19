<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure PPV/GPV defaults for packages that have NULL values
        DB::table('packages')
            ->whereNull('ppv_required')
            ->update([
                'ppv_required' => 0,
                'gpv_required' => 0,
                'required_sponsors_ppv_gpv' => 4,
                'rank_pv_enabled' => false,
            ]);

        // Ensure PPV/GPV defaults for users that have NULL values
        DB::table('users')
            ->whereNull('current_ppv')
            ->update([
                'current_ppv' => 0,
                'current_gpv' => 0,
                'ppv_gpv_updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed - this only sets defaults
    }
};
