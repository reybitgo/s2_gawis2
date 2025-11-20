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
        // Professional Package - MLM with medium quota
        DB::table('packages')->insert([
            'name' => 'Professional Package',
            'slug' => 'professional-package',
            'price' => 2500.00,
            'points_awarded' => 250,
            'quantity_available' => 9999,
            'short_description' => 'Enhanced MLM Package for serious entrepreneurs',
            'long_description' => 'Take your MLM business to the next level with the Professional Package. Enjoy higher commissions and expanded earning potential with our proven 5-level commission structure.',
            'is_active' => true,
            'sort_order' => 2,
            'is_mlm_package' => true,
            'max_mlm_levels' => 5,
            'monthly_quota_points' => 200.00,
            'enforce_monthly_quota' => true,
            'meta_data' => json_encode([
                'total_commission' => 1000.00,
                'company_profit' => 1500.00,
                'profit_margin' => '60%',
                'features' => [
                    'Enhanced MLM Business',
                    'Higher Commission Rates',
                    '5-Level Commission Structure',
                    'Priority Support',
                    'Advanced Network Tools',
                ],
                'commission_breakdown' => [
                    'level_1' => 500.00,
                    'level_2' => 125.00,
                    'level_3' => 125.00,
                    'level_4' => 125.00,
                    'level_5' => 125.00,
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Premium Package - MLM with high quota
        DB::table('packages')->insert([
            'name' => 'Premium Package',
            'slug' => 'premium-package',
            'price' => 5000.00,
            'points_awarded' => 500,
            'quantity_available' => 9999,
            'short_description' => 'Premium MLM Package for top performers',
            'long_description' => 'Unlock maximum earning potential with our Premium Package. Designed for leaders and top performers, this package offers the highest commission rates and exclusive benefits.',
            'is_active' => true,
            'sort_order' => 3,
            'is_mlm_package' => true,
            'max_mlm_levels' => 5,
            'monthly_quota_points' => 400.00,
            'enforce_monthly_quota' => true,
            'meta_data' => json_encode([
                'total_commission' => 2000.00,
                'company_profit' => 3000.00,
                'profit_margin' => '60%',
                'features' => [
                    'Premium MLM Business',
                    'Maximum Commission Rates',
                    '5-Level Commission Structure',
                    'VIP Support',
                    'Exclusive Training & Tools',
                ],
                'commission_breakdown' => [
                    'level_1' => 1000.00,
                    'level_2' => 250.00,
                    'level_3' => 250.00,
                    'level_4' => 250.00,
                    'level_5' => 250.00,
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Retail Package - Non-MLM for testing regular packages
        DB::table('packages')->insert([
            'name' => 'Retail Package',
            'slug' => 'retail-package',
            'price' => 500.00,
            'points_awarded' => 50,
            'quantity_available' => 9999,
            'short_description' => 'Regular retail package for personal use',
            'long_description' => 'Perfect for personal consumption or retail customers. This package does not include MLM commissions but offers great value for direct purchases.',
            'is_active' => true,
            'sort_order' => 4,
            'is_mlm_package' => false,
            'max_mlm_levels' => 0,
            'monthly_quota_points' => 0.00,
            'enforce_monthly_quota' => false,
            'meta_data' => json_encode([
                'features' => [
                    'Quality Products',
                    'Direct Purchase',
                    'No MLM Participation Required',
                    'Standard Support',
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the package IDs we just inserted
        $professionalPackage = DB::table('packages')->where('slug', 'professional-package')->first();
        $premiumPackage = DB::table('packages')->where('slug', 'premium-package')->first();

        // Create MLM commission settings for Professional Package
        if ($professionalPackage) {
            $mlmSettings = [
                ['package_id' => $professionalPackage->id, 'level' => 1, 'commission_amount' => 500.00, 'is_active' => true],
                ['package_id' => $professionalPackage->id, 'level' => 2, 'commission_amount' => 125.00, 'is_active' => true],
                ['package_id' => $professionalPackage->id, 'level' => 3, 'commission_amount' => 125.00, 'is_active' => true],
                ['package_id' => $professionalPackage->id, 'level' => 4, 'commission_amount' => 125.00, 'is_active' => true],
                ['package_id' => $professionalPackage->id, 'level' => 5, 'commission_amount' => 125.00, 'is_active' => true],
            ];

            foreach ($mlmSettings as $setting) {
                DB::table('mlm_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Create MLM commission settings for Premium Package
        if ($premiumPackage) {
            $mlmSettings = [
                ['package_id' => $premiumPackage->id, 'level' => 1, 'commission_amount' => 1000.00, 'is_active' => true],
                ['package_id' => $premiumPackage->id, 'level' => 2, 'commission_amount' => 250.00, 'is_active' => true],
                ['package_id' => $premiumPackage->id, 'level' => 3, 'commission_amount' => 250.00, 'is_active' => true],
                ['package_id' => $premiumPackage->id, 'level' => 4, 'commission_amount' => 250.00, 'is_active' => true],
                ['package_id' => $premiumPackage->id, 'level' => 5, 'commission_amount' => 250.00, 'is_active' => true],
            ];

            foreach ($mlmSettings as $setting) {
                DB::table('mlm_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete MLM settings for the packages we're removing
        $packages = DB::table('packages')
            ->whereIn('slug', ['professional-package', 'premium-package', 'retail-package'])
            ->get();
        
        foreach ($packages as $package) {
            DB::table('mlm_settings')->where('package_id', $package->id)->delete();
        }

        // Delete the packages
        DB::table('packages')->whereIn('slug', [
            'professional-package',
            'premium-package',
            'retail-package'
        ])->delete();
    }
};
