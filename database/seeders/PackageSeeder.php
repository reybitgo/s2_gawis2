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
        // Create the single MLM Starter Package
        $starterPackage = Package::create([
            'name' => 'Starter Package',
            'slug' => 'starter-package',
            'price' => 1000.00,
            'points_awarded' => 100,
            'quantity_available' => 9999, // Unlimited for MLM
            'short_description' => 'MLM Starter Package with 5-level commission structure',
            'long_description' => 'Join our Multi-Level Marketing program with the Starter Package. Earn commissions from your network across 5 levels: ₱200 from direct referrals (Level 1) and ₱50 from each of 4 indirect levels (Levels 2-5). Build your team and maximize your earnings potential!',
            'is_active' => true,
            'sort_order' => 1,
            'is_mlm_package' => true,
            'max_mlm_levels' => 5,
            'meta_data' => [
                'total_commission' => 400.00,
                'company_profit' => 600.00,
                'profit_margin' => '60%',
                'features' => [
                    'MLM Business Opportunity',
                    'Share Referral Links',
                    '5-Level Commission Structure',
                    'Withdrawable MLM Earnings',
                    'Network Visualization',
                ],
                'commission_breakdown' => [
                    'level_1' => 200.00,
                    'level_2' => 50.00,
                    'level_3' => 50.00,
                    'level_4' => 50.00,
                    'level_5' => 50.00,
                ],
            ],
        ]);

        $this->command->info("✅ Created Starter Package (ID: {$starterPackage->id}, Price: ₱{$starterPackage->price})");

        // Create MLM commission settings for 5 levels
        $mlmSettings = [
            ['package_id' => $starterPackage->id, 'level' => 1, 'commission_amount' => 200.00, 'is_active' => true],
            ['package_id' => $starterPackage->id, 'level' => 2, 'commission_amount' => 50.00, 'is_active' => true],
            ['package_id' => $starterPackage->id, 'level' => 3, 'commission_amount' => 50.00, 'is_active' => true],
            ['package_id' => $starterPackage->id, 'level' => 4, 'commission_amount' => 50.00, 'is_active' => true],
            ['package_id' => $starterPackage->id, 'level' => 5, 'commission_amount' => 50.00, 'is_active' => true],
        ];

        foreach ($mlmSettings as $setting) {
            MlmSetting::create($setting);
        }

        $this->command->info('✅ Created MLM commission structure:');
        $this->command->info('   Level 1 (Direct): ₱200.00');
        $this->command->info('   Level 2-5 (Indirect): ₱50.00 each');
        $this->command->info('   Total Commission: ₱400.00 (40%)');
        $this->command->info('   Company Profit: ₱600.00 (60%)');
    }
}
