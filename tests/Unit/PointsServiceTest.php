<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Package;
use App\Models\PointsTracker;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PointsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PointsService $pointsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointsService = app(PointsService::class);
    }

    public function test_creditppv_increments_user_ppv(): void
    {
        $user = User::factory()->create(['current_ppv' => 0, 'current_gpv' => 0]);
        $orderItem = OrderItem::factory()->create();
        $product = Product::factory()->create(['points_awarded' => 50]);

        $this->pointsService->creditPPV($user, 100, $orderItem);

        $user->refresh();
        $this->assertEquals(100, $user->current_ppv);
        $this->assertDatabaseHas('points_tracker', [
            'user_id' => $user->id,
            'ppv' => 100,
            'point_type' => 'product_purchase',
        ]);
    }

    public function test_creditppv_updates_ppv_gpv_updated_at(): void
    {
        $user = User::factory()->create(['current_ppv' => 0, 'current_gpv' => 0, 'ppv_gpv_updated_at' => null]);
        $orderItem = OrderItem::factory()->create();

        $this->pointsService->creditPPV($user, 50, $orderItem);

        $user->refresh();
        $this->assertNotNull($user->ppv_gpv_updated_at);
    }

    public function test_creditppv_creates_points_tracker_entry(): void
    {
        $user = User::factory()->create();
        $orderItem = OrderItem::factory()->create();

        $this->pointsService->creditPPV($user, 75, $orderItem);

        $this->assertDatabaseHas('points_tracker', [
            'user_id' => $user->id,
            'order_item_id' => $orderItem->id,
            'ppv' => 75,
            'gpv' => 0,
            'point_type' => 'product_purchase',
        ]);
    }

    public function test_creditgpvtouplines_credits_buyer_gpv(): void
    {
        $user = User::factory()->create(['current_ppv' => 0, 'current_gpv' => 0]);
        $orderItem = OrderItem::factory()->create();

        $this->pointsService->creditGPVToUplines($user, 50, $orderItem);

        $user->refresh();
        $this->assertEquals(50, $user->current_gpv);
    }

    public function test_creditgpvtouplines_credits_all_uplines(): void
    {
        $sponsor = User::factory()->create(['current_ppv' => 0, 'current_gpv' => 0]);
        $level1User = User::factory()->create(['sponsor_id' => $sponsor->id, 'current_ppv' => 0, 'current_gpv' => 0]);
        $level2User = User::factory()->create(['sponsor_id' => $level1User->id, 'current_ppv' => 0, 'current_gpv' => 0]);
        $orderItem = OrderItem::factory()->create();

        $this->pointsService->creditGPVToUplines($level2User, 100, $orderItem);

        $sponsor->refresh();
        $level1User->refresh();
        $level2User->refresh();

        $this->assertEquals(100, $sponsor->current_gpv);
        $this->assertEquals(100, $level1User->current_gpv);
        $this->assertEquals(100, $level2User->current_gpv);
    }

    public function test_creditgpvtouplines_records_tracker_entries(): void
    {
        $sponsor = User::factory()->create();
        $buyer = User::factory()->create(['sponsor_id' => $sponsor->id]);
        $orderItem = OrderItem::factory()->create();

        $this->pointsService->creditGPVToUplines($buyer, 30, $orderItem);

        $this->assertDatabaseHas('points_tracker', [
            'user_id' => $buyer->id,
            'order_item_id' => $orderItem->id,
            'gpv' => 30,
            'awarded_to_user_id' => null,
        ]);

        $this->assertDatabaseHas('points_tracker', [
            'user_id' => $sponsor->id,
            'order_item_id' => $orderItem->id,
            'ppv' => 0,
            'gpv' => 30,
            'awarded_to_user_id' => $buyer->id,
        ]);
    }

    public function test_processorderpoints_credits_ppv_and_gpv(): void
    {
        $user = User::factory()->create(['current_ppv' => 0, 'current_gpv' => 0]);
        $product = Product::factory()->create(['points_awarded' => 25]);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->pointsService->processOrderPoints($order);

        $user->refresh();
        $this->assertEquals(50, $user->current_ppv);
        $this->assertEquals(50, $user->current_gpv);
    }

    public function test_processorderpoints_skips_zero_point_products(): void
    {
        $user = User::factory()->create(['current_ppv' => 10, 'current_gpv' => 20]);
        $product = Product::factory()->create(['points_awarded' => 0]);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->pointsService->processOrderPoints($order);

        $user->refresh();
        $this->assertEquals(10, $user->current_ppv);
        $this->assertEquals(20, $user->current_gpv);
    }

    public function test_resetppvgpvonrankadvancement_resets_to_zero(): void
    {
        $user = User::factory()->create(['current_ppv' => 100, 'current_gpv' => 1000, 'current_rank' => 'Starter']);

        $this->pointsService->resetPPVGPVOnRankAdvancement($user);

        $user->refresh();
        $this->assertEquals(0, $user->current_ppv);
        $this->assertEquals(0, $user->current_gpv);
    }

    public function test_resetppvgpvonrankadvancement_creates_negative_tracker_entries(): void
    {
        $user = User::factory()->create(['current_ppv' => 75, 'current_gpv' => 500, 'current_rank' => 'Newbie']);

        $this->pointsService->resetPPVGPVOnRankAdvancement($user);

        $this->assertDatabaseHas('points_tracker', [
            'user_id' => $user->id,
            'ppv' => -75,
            'gpv' => -500,
            'point_type' => 'rank_advancement_reset',
            'rank_at_time' => 'Newbie',
            'awarded_to_user_id' => $user->id,
        ]);
    }

    public function test_resetppvgpvonrankadvancement_updates_timestamp(): void
    {
        $user = User::factory()->create(['current_ppv' => 50, 'current_gpv' => 200, 'ppv_gpv_updated_at' => null]);

        $this->pointsService->resetPPVGPVOnRankAdvancement($user);

        $user->refresh();
        $this->assertNotNull($user->ppv_gpv_updated_at);
    }

    public function test_processorderpoints_wraps_in_transaction(): void
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $user = User::factory()->create();
        $product = Product::factory()->create(['points_awarded' => 10]);
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->pointsService->processOrderPoints($order);
    }
}
