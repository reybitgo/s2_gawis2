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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sponsor_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            $table->string('referral_code', 20)->nullable()->after('sponsor_id');

            // Add indexes for performance
            $table->index('sponsor_id');
        });

        // Generate referral codes for existing users
        $users = DB::table('users')->whereNull('referral_code')->orWhere('referral_code', '')->get();
        foreach ($users as $user) {
            $referralCode = 'REF' . strtoupper(\Illuminate\Support\Str::random(8));
            // Ensure uniqueness
            while (DB::table('users')->where('referral_code', $referralCode)->exists()) {
                $referralCode = 'REF' . strtoupper(\Illuminate\Support\Str::random(8));
            }
            DB::table('users')->where('id', $user->id)->update(['referral_code' => $referralCode]);
        }

        // Now add unique constraint after all users have referral codes
        Schema::table('users', function (Blueprint $table) {
            $table->unique('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sponsor_id']);
            $table->dropIndex(['sponsor_id']);
            $table->dropIndex(['referral_code']);
            $table->dropColumn(['sponsor_id', 'referral_code']);
        });
    }
};
