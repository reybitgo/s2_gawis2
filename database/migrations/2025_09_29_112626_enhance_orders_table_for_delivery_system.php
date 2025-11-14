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
        Schema::table('orders', function (Blueprint $table) {
            // Add delivery method
            $table->enum('delivery_method', ['office_pickup', 'home_delivery'])
                  ->default('office_pickup')
                  ->after('payment_status');

            // Pickup/Delivery specific fields
            $table->string('tracking_number')->nullable()->after('delivery_method');
            $table->string('courier_name')->nullable()->after('tracking_number');
            $table->timestamp('pickup_date')->nullable()->after('courier_name');
            $table->string('pickup_location')->nullable()->after('pickup_date');
            $table->text('pickup_instructions')->nullable()->after('pickup_location');
            $table->timestamp('estimated_delivery')->nullable()->after('pickup_instructions');
            $table->text('admin_notes')->nullable()->after('estimated_delivery');
            $table->text('status_message')->nullable()->after('admin_notes');
        });

        // Update status enum to include all new statuses
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending', 'paid', 'payment_failed', 'processing', 'confirmed', 'packing',
                'ready_for_pickup', 'pickup_notified', 'received_in_office',
                'ready_to_ship', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'delivery_failed',
                'completed', 'on_hold', 'cancelled', 'refunded', 'returned', 'failed'
            ) DEFAULT 'pending'");
        } else {
            // For other databases, the status is already a string, so no change needed
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'tracking_number',
                'courier_name',
                'pickup_date',
                'pickup_location',
                'pickup_instructions',
                'estimated_delivery',
                'admin_notes',
                'status_message'
            ]);
        });

        // Revert status enum
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending', 'paid', 'processing', 'completed', 'cancelled', 'failed'
            ) DEFAULT 'pending'");
        }
    }
};