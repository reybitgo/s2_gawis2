<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Package;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RankAdvancement;
use App\Models\DirectSponsorsTracker;
use App\Services\RankAdvancementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class DualPathRankAdvancementTest extends \Tests\TestCase
{
    use RefreshDatabase;

    protected RankAdvancementService $rankService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rankService = app(RankAdvancementService::class);
        $this->seed(\Database\Seeders\PackageSeeder::class);
        Auth::loginUsingId(1);
    }

    public function test_path_a_advancement_with_required_sponsors(): void
    {
        $currentRank = Package::updateOrCreate(
            ['slug' => 'starter-package'],
            [
                'name' => 'Starter Package',
                'rank_name' => 'Starter',
                'rank_order' => 1,
                'price' => 1000.00,
                'required_direct_sponsors' => 5,
                'required_sponsors_ppv_gpv' => 4,
                'ppv_required' => 100,
                'gpv_required' => 1000,
                'rank_pv_enabled' => true,
                'is_mlm_package' => true,
                'is_rankable' => true,
                'is_active' => true,
            ]
        );
        $nextRank = Package::updateOrCreate(
            ['slug' => 'newbie-package'],
            [
                'name' => 'Newbie Package',
                'rank_name' => 'Newbie',
                'rank_order' => 2,
            ]
        );
        
        $currentRank->update(['next_rank_package_id' => $nextRank->id]);
        
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
        ]);

        for ($i = 0; $i < 5; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertEquals($nextRank->rank_name, $user->current_rank);

        $advancement = RankAdvancement::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($advancement);
        $this->assertEquals('recruitment_based', $advancement->advancement_type);
    }

    public function test_path_b_advancement_with_ppv_and_gpv(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $nextRank = Package::where('rank_order', 2)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 100,
            'current_gpv' => 50000,
        ]);

        for ($i = 0; $i < $currentRank->required_sponsors_ppv_gpv; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertEquals($nextRank->rank_name, $user->current_rank);

        $advancement = RankAdvancement::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($advancement);
        $this->assertEquals('pv_based', $advancement->advancement_type);
        $this->assertEquals(0, $user->fresh()->current_ppv);
        $this->assertEquals(0, $user->fresh()->current_gpv);
    }

    public function test_path_b_fails_without_required_sponsors(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 100,
            'current_gpv' => 1000,
        ]);

        User::factory()->create([
            'sponsor_id' => $user->id,
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
        ]);

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertFalse($result);
        $user->refresh();
        $this->assertEquals($currentRank->rank_name, $user->current_rank);
    }

    public function test_path_b_fails_without_required_ppv(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 50,
            'current_gpv' => 1000,
        ]);

        for ($i = 0; $i < $currentRank->required_sponsors_ppv_gpv; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertFalse($result);
        $user->refresh();
        $this->assertEquals($currentRank->rank_name, $user->current_rank);
    }

    public function test_path_b_fails_without_required_gpv(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 100,
            'current_gpv' => 500,
        ]);

        for ($i = 0; $i < $currentRank->required_sponsors_ppv_gpv; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertFalse($result);
        $user->refresh();
        $this->assertEquals($currentRank->rank_name, $user->current_rank);
    }

    public function test_pv_disabled_skips_path_b_checks(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $currentRank->update(['rank_pv_enabled' => false]);
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 100,
            'current_gpv' => 1000,
        ]);

        for ($i = 0; $i < $currentRank->required_sponsors_ppv_gpv; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertFalse($result);
        $user->refresh();
        $this->assertEquals($currentRank->rank_name, $user->current_rank);
    }

    public function test_same_rank_sponsor_count(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
        ]);

        for ($i = 0; $i < 4; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $this->assertEquals(4, $progress['path_a']['sponsors_count']);
        $this->assertEquals($currentRank->required_direct_sponsors, $progress['path_a']['required_sponsors']);
    }

    public function test_path_b_sponsors_count_excludes_different_ranks(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
        ]);

        User::factory()->create([
            'sponsor_id' => $user->id,
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
        ]);

        $nextRank = Package::where('rank_order', 2)->first();
        User::factory()->create([
            'sponsor_id' => $user->id,
            'current_rank' => $nextRank->rank_name,
            'rank_package_id' => $nextRank->id,
        ]);

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $this->assertEquals(2, $progress['path_b']['directs_ppv_gpv']['current']);
    }

    public function test_rank_advancement_resets_ppv_and_gpv(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $nextRank = Package::where('rank_order', 2)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 100,
            'current_gpv' => 1000,
        ]);

        for ($i = 0; $i < $currentRank->required_sponsors_ppv_gpv; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $this->rankService->checkAndTriggerAdvancement($user);

        $user->refresh();
        $this->assertEquals($nextRank->rank_name, $user->current_rank);
        $this->assertEquals(0, $user->current_ppv);
        $this->assertEquals(0, $user->current_gpv);
    }

    public function test_path_a_wins_when_both_paths_met(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $nextRank = Package::where('rank_order', 2)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
        ]);

        for ($i = 0; $i < $currentRank->required_direct_sponsors; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        for ($i = 0; $i < $currentRank->required_sponsors_ppv_gpv; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $user->update([
            'current_ppv' => 100,
            'current_gpv' => 1000,
        ]);

        $result = $this->rankService->checkAndTriggerAdvancement($user);

        $this->assertTrue($result);
        $advancement = RankAdvancement::where('user_id', $user->id)->latest()->first();
        $this->assertEquals('recruitment_based', $advancement->advancement_type);
    }

    public function test_get_rank_progress_returns_path_a_and_path_b(): void
    {
        $currentRank = Package::where('rank_order', 2)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 50,
            'current_gpv' => 500,
        ]);

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $this->assertArrayHasKey('path_a', $progress);
        $this->assertArrayHasKey('path_b', $progress);
        $this->assertArrayHasKey('is_eligible', $progress);
        $this->assertArrayHasKey('current_rank', $progress);
        $this->assertArrayHasKey('rank_pv_enabled', $progress);
    }

    public function test_path_b_directs_ppv_gpv_progress_calculation(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 50,
            'current_gpv' => 500,
        ]);

        for ($i = 0; $i < 2; $i++) {
            User::factory()->create([
                'sponsor_id' => $user->id,
                'current_rank' => $currentRank->rank_name,
                'rank_package_id' => $currentRank->id,
            ]);
        }

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $expectedPercentage = (2 / 4) * 100;
        $this->assertEquals($expectedPercentage, $progress['path_b']['directs_ppv_gpv']['progress']);
    }

    public function test_path_b_ppv_progress_calculation(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 50,
            'current_gpv' => 500,
        ]);

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $expectedPercentage = (50 / 100) * 100;
        $this->assertEquals($expectedPercentage, $progress['path_b']['ppv']['progress']);
    }

    public function test_path_b_gpv_progress_calculation(): void
    {
        $currentRank = Package::where('rank_order', 1)->first();
        $user = User::factory()->create([
            'current_rank' => $currentRank->rank_name,
            'rank_package_id' => $currentRank->id,
            'current_ppv' => 50,
            'current_gpv' => 500,
        ]);

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $expectedPercentage = (500 / 1000) * 100;
        $this->assertEquals($expectedPercentage, $progress['path_b']['gpv']['progress']);
    }

    public function test_get_rank_progress_returns_zero_for_unranked_user(): void
    {
        $user = User::factory()->create([
            'current_rank' => null,
            'rank_package_id' => null,
            'current_ppv' => 0,
            'current_gpv' => 0,
        ]);

        $progress = $this->rankService->getRankAdvancementProgress($user);

        $this->assertEquals('Unranked', $progress['current_rank']);
        $this->assertFalse($progress['is_eligible']);
        $this->assertFalse($progress['can_advance']);
    }
}
