<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rank_advancements MODIFY COLUMN advancement_type ENUM(
            'purchase',
            'sponsorship_reward',
            'admin_adjustment',
            'recruitment_based',
            'pv_based'
        ) DEFAULT 'purchase'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rank_advancements MODIFY COLUMN advancement_type ENUM(
            'purchase',
            'sponsorship_reward',
            'admin_adjustment'
        ) DEFAULT 'purchase'");
    }
};
