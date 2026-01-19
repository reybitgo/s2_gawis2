<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE packages SET required_sponsors_ppv_gpv = 4 WHERE is_rankable = 1");

        DB::statement("UPDATE packages SET ppv_required = 0 WHERE rank_order = 1");
        DB::statement("UPDATE packages SET ppv_required = 100, gpv_required = 1000 WHERE rank_order = 2");
        DB::statement("UPDATE packages SET ppv_required = 300, gpv_required = 5000 WHERE rank_order = 3");
        DB::statement("UPDATE packages SET ppv_required = 500, gpv_required = 15000 WHERE rank_order = 4");
        DB::statement("UPDATE packages SET ppv_required = 800, gpv_required = 40000 WHERE rank_order = 5");
        DB::statement("UPDATE packages SET ppv_required = 1200, gpv_required = 100000 WHERE rank_order = 6");
        DB::statement("UPDATE packages SET ppv_required = 2000, gpv_required = 250000 WHERE rank_order = 7");
    }

    public function down(): void
    {
        DB::statement("UPDATE packages SET required_sponsors_ppv_gpv = 0 WHERE is_rankable = 1");
        DB::statement("UPDATE packages SET ppv_required = 0 WHERE is_rankable = 1");
        DB::statement("UPDATE packages SET gpv_required = 0 WHERE is_rankable = 1");
    }
};
