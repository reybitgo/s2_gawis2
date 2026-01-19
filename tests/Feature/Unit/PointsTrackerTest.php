<?php

namespace Tests\Unit;

use App\Models\PointsTracker;
use App\Models\User;
use App\Models\OrderItem;
use Tests\TestCase;

class PointsTrackerTest extends TestCase
{
    public function test_points_tracker_has_fillable_attributes(): void
    {
        $tracker = new PointsTracker([
            'user_id' => 1,
            'order_item_id' => 1,
            'ppv' => 100.50,
            'gpv' => 1000.25,
            'point_type' => 'product_purchase',
        ]);

        $this->assertEquals(1, $tracker->user_id);
        $this->assertEquals(1, $tracker->order_item_id);
        $this->assertEquals(100.50, $tracker->ppv);
        $this->assertEquals(1000.25, $tracker->gpv);
        $this->assertEquals('product_purchase', $tracker->point_type);
    }

    public function test_points_tracker_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $tracker = PointsTracker::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $tracker->user);
    }

    public function test_points_tracker_belongs_to_order_item(): void
    {
        $orderItem = OrderItem::factory()->create();
        $tracker = PointsTracker::factory()->create(['order_item_id' => $orderItem->id]);

        $this->assertInstanceOf(OrderItem::class, $tracker->orderItem);
    }

    public function test_points_tracker_belongs_to_awarded_to_user(): void
    {
        $user = User::factory()->create();
        $tracker = PointsTracker::factory()->create(['awarded_to_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $tracker->awardedToUser);
    }

    public function test_scope_ppv_filters_positive_ppv(): void
    {
        PointsTracker::factory()->create(['ppv' => 50]);
        PointsTracker::factory()->create(['ppv' => 0]);
        PointsTracker::factory()->create(['ppv' => -10]);

        $ppvTrackers = PointsTracker::ppv()->get();

        $this->assertCount(1, $ppvTrackers);
        $this->assertEquals(50, $ppvTrackers->first()->ppv);
    }

    public function scope_gpv_filters_positive_gpv(): void
    {
        PointsTracker::factory()->create(['gpv' => 500]);
        PointsTracker::factory()->create(['gpv' => 0]);
        PointsTracker::factory()->create(['gpv' => -100]);

        $gpvTrackers = PointsTracker::gpv()->get();

        $this->assertCount(1, $gpvTrackers);
        $this->assertEquals(500, $gpvTrackers->first()->gpv);
    }

    public function test_ppv_casts_to_decimal(): void
    {
        $tracker = new PointsTracker(['ppv' => 100.123]);

        $this->assertEquals(100.12, $tracker->ppv);
    }

    public function test_gpv_casts_to_decimal(): void
    {
        $tracker = new PointsTracker(['gpv' => 1000.456]);

        $this->assertEquals(1000.46, $tracker->gpv);
    }

    public function test_earned_at_casts_to_datetime(): void
    {
        $tracker = new PointsTracker(['earned_at' => '2024-01-15 10:30:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $tracker->earned_at);
    }

    public function test_has_no_timestamps(): void
    {
        $tracker = new PointsTracker();

        $this->assertFalse($tracker->exists);
    }
}
