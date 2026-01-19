<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationPointsDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_cancellation_deducts_points()
    {
        $user = User::factory()->create(['current_ppv' => 100, 'current_gpv' => 500]);
        $product = Product::factory()->create(['points_awarded' => 25]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'points_credited' => true,
            'status' => 'paid',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $order->cancel('Test cancellation');

        $user->refresh();
        $order->refresh();

        $this->assertEquals(50, $user->current_ppv);
        $this->assertEquals(450, $user->current_gpv);
        $this->assertFalse($order->points_credited);
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_order_cancellation_deducts_gpv_from_uplines()
    {
        $sponsor = User::factory()->create(['current_ppv' => 0, 'current_gpv' => 200]);
        $buyer = User::factory()->create(['sponsor_id' => $sponsor->id, 'current_ppv' => 100, 'current_gpv' => 500]);
        $product = Product::factory()->create(['points_awarded' => 25]);
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'points_credited' => true,
            'status' => 'paid',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $order->cancel('Test cancellation');

        $buyer->refresh();
        $sponsor->refresh();

        $this->assertEquals(50, $buyer->current_ppv);
        $this->assertEquals(450, $buyer->current_gpv);
        $this->assertEquals(150, $sponsor->current_gpv);
    }

    public function test_order_cancellation_skips_uncredited_orders()
    {
        $user = User::factory()->create(['current_ppv' => 100, 'current_gpv' => 500]);
        $product = Product::factory()->create(['points_awarded' => 25]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'points_credited' => false,
            'status' => 'paid',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $order->cancel('Test cancellation');

        $user->refresh();

        $this->assertEquals(100, $user->current_ppv);
        $this->assertEquals(500, $user->current_gpv);
    }
}
