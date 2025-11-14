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
        // Add return-related statuses and remove 'in_transit' from orders.status enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'paid',
            'payment_failed',
            'processing',
            'confirmed',
            'packing',
            'ready_for_pickup',
            'pickup_notified',
            'received_in_office',
            'ready_to_ship',
            'shipped',
            'out_for_delivery',
            'delivered',
            'delivery_failed',
            'return_requested',
            'return_approved',
            'return_rejected',
            'return_in_transit',
            'return_received',
            'completed',
            'on_hold',
            'cancelled',
            'refunded',
            'returned',
            'failed'
        ) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove return-related statuses from orders.status enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'paid',
            'payment_failed',
            'processing',
            'confirmed',
            'packing',
            'ready_for_pickup',
            'pickup_notified',
            'received_in_office',
            'ready_to_ship',
            'shipped',
            'in_transit',
            'out_for_delivery',
            'delivered',
            'delivery_failed',
            'completed',
            'on_hold',
            'cancelled',
            'refunded',
            'returned',
            'failed'
        ) NOT NULL DEFAULT 'pending'");
    }
};
