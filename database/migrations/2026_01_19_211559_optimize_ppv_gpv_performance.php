<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Additional indexes for common query patterns
        try {
            Schema::table('points_tracker', function (Blueprint $table) {
                $table->index('order_item_id', 'points_tracker_order_item_id_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        try {
            Schema::table('points_tracker', function (Blueprint $table) {
                $table->index('rank_at_time', 'points_tracker_rank_at_time_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        try {
            Schema::table('points_tracker', function (Blueprint $table) {
                $table->index('earned_at', 'points_tracker_earned_at_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        try {
            Schema::table('points_tracker', function (Blueprint $table) {
                $table->index(['user_id', 'point_type'], 'points_tracker_user_type_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        // Additional indexes for rank advancement queries
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['current_rank', 'ppv_gpv_updated_at'], 'users_rank_ppv_time_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('current_ppv', 'users_current_ppv_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('current_gpv', 'users_current_gpv_index');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }

        // Index for sponsor traversal (might already exist)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('sponsor_id', 'users_sponsor_id_optimized');
            });
        } catch (\Exception $e) {
            // Index already exists, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points_tracker', function (Blueprint $table) {
            $table->dropIndex('points_tracker_order_item_id_index');
            $table->dropIndex('points_tracker_rank_at_time_index');
            $table->dropIndex('points_tracker_earned_at_index');
            $table->dropIndex('points_tracker_user_type_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_rank_ppv_time_index');
            $table->dropIndex('users_current_ppv_index');
            $table->dropIndex('users_current_gpv_index');
            $table->dropIndex('users_sponsor_id_optimized');
        });
    }
};
