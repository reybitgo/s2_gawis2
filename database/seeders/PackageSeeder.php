<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\MlmSetting;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create MLM Starter Package (Rank 1)
        $starterPackage = Package::create([
            'name' => 'Starter Package',
            'slug' => 'starter-package',
            'rank_name' => 'Starter',
            'rank_order' => 1,
            'price' => 1000.00,
            'required_direct_sponsors' => 5, // Need 5 Starter-rank sponsors to advance
            'is_mlm_package' => true,
            'is_rankable' => true,
            'max_mlm_levels' => 5,
            'monthly_quota_points' => 100.00, // FOR UNILEVEL BONUSES ONLY
            'enforce_monthly_quota' => true, // FOR UNILEVEL BONUSES ONLY
            'points_awarded' => 100,
            'quantity_available' => 9999,
            'short_description' => 'Starter Rank - Entry level MLM package',
            'long_description' => 'Join our MLM program at the Starter rank. Earn commissions from your network across 5 levels. Sponsor 5 Starter-rank members to automatically advance to Newbie rank!',
            'is_active' => true,
            'sort_order' => 1,
            'next_rank_package_id' => null, // Will set after creating next package
            'meta_data' => [
                'total_commission' => 400.00,
                'company_profit' => 600.00,
                'features' => [
                    'Starter Rank Package',
                    'MLM Business Opportunity',
                    '5-Level Commission Structure',
                    'Auto-advance to Newbie after 5 sponsors',
                ],
            ],
        ]);

        $this->command->info("✅ Created Starter Package (Rank 1) - ID: {$starterPackage->id}");

        // Create MLM Newbie Package (Rank 2)
        $newbiePackage = Package::create([
            'name' => 'Newbie Package',
            'slug' => 'newbie-package',
            'rank_name' => 'Newbie',
            'rank_order' => 2,
            'price' => 2500.00,
            'required_direct_sponsors' => 8, // Need 8 Newbie-rank sponsors to advance
            'is_mlm_package' => true,
            'is_rankable' => true,
            'max_mlm_levels' => 5,
            'monthly_quota_points' => 150.00, // FOR UNILEVEL BONUSES ONLY
            'enforce_monthly_quota' => true, // FOR UNILEVEL BONUSES ONLY
            'points_awarded' => 250,
            'quantity_available' => 9999,
            'short_description' => 'Newbie Rank - Enhanced MLM package with higher commissions',
            'long_description' => 'Advance to Newbie rank for higher earnings. Sponsor 8 Newbie-rank members to automatically advance to Bronze rank!',
            'is_active' => true,
            'sort_order' => 2,
            'next_rank_package_id' => null, // Will set after creating next package
            'meta_data' => [
                'total_commission' => 800.00,
                'company_profit' => 1700.00,
                'features' => [
                    'Newbie Rank Package',
                    'Higher Commission Rates',
                    '5-Level Commission Structure',
                    'Auto-advance to Bronze after 8 sponsors',
                ],
            ],
        ]);

        $this->command->info("✅ Created Newbie Package (Rank 2) - ID: {$newbiePackage->id}");

        // Create MLM Bronze Package (Rank 3 - Top Rank)
        $bronzePackage = Package::create([
            'name' => 'Bronze Package',
            'slug' => 'bronze-package',
            'rank_name' => 'Bronze',
            'rank_order' => 3,
            'price' => 5000.00,
            'required_direct_sponsors' => 10, // Top rank requirement
            'is_mlm_package' => true,
            'is_rankable' => true,
            'max_mlm_levels' => 5,
            'monthly_quota_points' => 200.00, // FOR UNILEVEL BONUSES ONLY
            'enforce_monthly_quota' => true, // FOR UNILEVEL BONUSES ONLY
            'points_awarded' => 500,
            'quantity_available' => 9999,
            'short_description' => 'Bronze Rank - Top tier MLM package with maximum commissions',
            'long_description' => 'Reach Bronze rank, the top tier in our MLM system. Enjoy the highest commission rates and maximum earning potential!',
            'is_active' => true,
            'sort_order' => 3,
            'next_rank_package_id' => null, // Top rank - no next package
            'meta_data' => [
                'total_commission' => 1600.00,
                'company_profit' => 3400.00,
                'features' => [
                    'Bronze Rank Package (Top Tier)',
                    'Maximum Commission Rates',
                    '5-Level Commission Structure',
                    'Elite Status',
                ],
            ],
        ]);

        $this->command->info("✅ Created Bronze Package (Rank 3 - Top Rank) - ID: {$bronzePackage->id}");

        // Set next_rank_package_id relationships
        $starterPackage->update(['next_rank_package_id' => $newbiePackage->id]);
        $newbiePackage->update(['next_rank_package_id' => $bronzePackage->id]);

        $this->command->info('✅ Set rank progression: Starter → Newbie → Bronze');

        // Create MLM settings for Starter Package
        $this->createMLMSettings($starterPackage->id, [
            1 => 200, // Level 1
            2 => 50,  // Level 2
            3 => 50,  // Level 3
            4 => 50,  // Level 4
            5 => 50,  // Level 5
        ], 'Starter');

        // Create MLM settings for Newbie Package
        $this->createMLMSettings($newbiePackage->id, [
            1 => 400, // Higher commission for higher rank
            2 => 100,
            3 => 100,
            4 => 100,
            5 => 100,
        ], 'Newbie');

        // Create MLM settings for Bronze Package
        $this->createMLMSettings($bronzePackage->id, [
            1 => 800, // Maximum commission for top rank
            2 => 200,
            3 => 200,
            4 => 200,
            5 => 200,
        ], 'Bronze');
    }

    /**
     * Create MLM settings for a package
     */
    private function createMLMSettings($packageId, $levels, $rankName)
    {
        foreach ($levels as $level => $commission) {
            MlmSetting::create([
                'package_id' => $packageId,
                'level' => $level,
                'commission_amount' => $commission,
                'is_active' => true,
            ]);
        }

        $total = array_sum($levels);
        $this->command->info("✅ Created MLM commission structure for {$rankName}:");
        $this->command->info("   Level 1: ₱{$levels[1]}.00");
        $this->command->info("   Levels 2-5: ₱{$levels[2]}.00 each");
        $this->command->info("   Total Commission: ₱{$total}.00");
    }
}
