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
            $table->string('payment_preference')->nullable();
            $table->string('gcash_number', 11)->nullable();
            $table->string('maya_number', 11)->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('other_payment_method')->nullable();
            $table->text('other_payment_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'payment_preference',
                'gcash_number',
                'maya_number',
                'pickup_location',
                'other_payment_method',
                'other_payment_details',
            ]);
        });
    }
};
