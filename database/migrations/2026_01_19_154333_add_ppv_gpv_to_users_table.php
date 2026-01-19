<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('current_ppv', 10, 2)->default(0)->after('rank_updated_at')->comment('Current Personal Points Volume');
            $table->decimal('current_gpv', 10, 2)->default(0)->after('current_ppv')->comment('Current Group Points Volume');
            $table->timestamp('ppv_gpv_updated_at')->nullable()->after('current_gpv')->comment('Last time PPV/GPV was calculated');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'current_ppv',
                'current_gpv',
                'ppv_gpv_updated_at',
            ]);
        });
    }
};
