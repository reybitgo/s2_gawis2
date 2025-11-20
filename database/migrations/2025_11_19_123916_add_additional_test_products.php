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
        // Product 1: Moringa Powder - High PV
        DB::table('products')->insert([
            'name' => 'Moringa Powder',
            'slug' => 'moringa-powder',
            'price' => 800.00,
            'points_awarded' => 15.50,
            'quantity_available' => 150,
            'short_description' => 'Pure organic moringa leaf powder rich in vitamins and minerals',
            'long_description' => 'Our premium Moringa Powder is sourced from organic farms and packed with essential nutrients. Known as the "miracle tree," moringa contains high levels of vitamin C, calcium, protein, iron, and amino acids. Perfect for smoothies, teas, or cooking.',
            'is_active' => true,
            'sort_order' => 5,
            'sku' => 'MOR-PWD-001',
            'category' => 'Supplements',
            'weight_grams' => 250,
            'total_unilevel_bonus' => 130.00,
            'meta_data' => json_encode([
                'benefits' => [
                    'Rich in antioxidants',
                    'Supports immune system',
                    'Natural energy boost',
                    'Anti-inflammatory properties',
                ],
                'serving_size' => '1 teaspoon (5g)',
                'servings_per_container' => 50,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Product 2: Turmeric Capsules - Medium PV
        DB::table('products')->insert([
            'name' => 'Turmeric Capsules',
            'slug' => 'turmeric-capsules',
            'price' => 650.00,
            'points_awarded' => 12.00,
            'quantity_available' => 200,
            'short_description' => 'Curcumin-rich turmeric extract for joint health and inflammation',
            'long_description' => 'Experience the powerful anti-inflammatory benefits of our Turmeric Capsules. Each capsule contains high-potency curcumin extract with black pepper for enhanced absorption. Supports joint health, reduces inflammation, and promotes overall wellness.',
            'is_active' => true,
            'sort_order' => 6,
            'sku' => 'TUR-CAP-001',
            'category' => 'Health & Wellness',
            'weight_grams' => 120,
            'total_unilevel_bonus' => 100.00,
            'meta_data' => json_encode([
                'benefits' => [
                    'Supports joint health',
                    'Natural anti-inflammatory',
                    'Boosts brain function',
                    'Heart health support',
                ],
                'dosage' => '2 capsules daily',
                'capsules_per_bottle' => 60,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Product 3: Omega-3 Fish Oil - High PV
        DB::table('products')->insert([
            'name' => 'Omega-3 Fish Oil',
            'slug' => 'omega-3-fish-oil',
            'price' => 1100.00,
            'points_awarded' => 18.25,
            'quantity_available' => 100,
            'short_description' => 'Premium fish oil rich in EPA and DHA for heart and brain health',
            'long_description' => 'Our Omega-3 Fish Oil is molecularly distilled for purity and contains high concentrations of EPA and DHA. Supports cardiovascular health, brain function, and reduces inflammation. Mercury-free and third-party tested for quality.',
            'is_active' => true,
            'sort_order' => 7,
            'sku' => 'OMG-FSH-001',
            'category' => 'Supplements',
            'weight_grams' => 180,
            'total_unilevel_bonus' => 150.00,
            'meta_data' => json_encode([
                'benefits' => [
                    'Heart health support',
                    'Brain function enhancement',
                    'Joint inflammation reduction',
                    'Eye health support',
                ],
                'dosage' => '2 softgels daily',
                'softgels_per_bottle' => 120,
                'epa_dha_per_serving' => '1000mg',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Product 4: Green Tea Extract - Low-Medium PV
        DB::table('products')->insert([
            'name' => 'Green Tea Extract',
            'slug' => 'green-tea-extract',
            'price' => 550.00,
            'points_awarded' => 9.50,
            'quantity_available' => 180,
            'short_description' => 'Standardized green tea extract with EGCG for metabolism and antioxidant support',
            'long_description' => 'Harness the power of green tea with our concentrated extract capsules. Standardized to 50% EGCG, our formula supports metabolism, provides powerful antioxidants, and promotes healthy weight management. Each capsule equals approximately 5 cups of green tea.',
            'is_active' => true,
            'sort_order' => 8,
            'sku' => 'GRN-TEA-001',
            'category' => 'Health & Wellness',
            'weight_grams' => 100,
            'total_unilevel_bonus' => 85.00,
            'meta_data' => json_encode([
                'benefits' => [
                    'Metabolism support',
                    'Powerful antioxidants',
                    'Weight management aid',
                    'Energy without jitters',
                ],
                'dosage' => '1-2 capsules daily',
                'capsules_per_bottle' => 90,
                'egcg_content' => '250mg per capsule',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the product IDs we just inserted
        $moringaProduct = DB::table('products')->where('slug', 'moringa-powder')->first();
        $turmericProduct = DB::table('products')->where('slug', 'turmeric-capsules')->first();
        $omegaProduct = DB::table('products')->where('slug', 'omega-3-fish-oil')->first();
        $greenTeaProduct = DB::table('products')->where('slug', 'green-tea-extract')->first();

        // Create Unilevel settings for Moringa Powder (Total: 130.00)
        if ($moringaProduct) {
            $unilevelSettings = [
                ['product_id' => $moringaProduct->id, 'level' => 1, 'bonus_amount' => 55.00, 'is_active' => true],
                ['product_id' => $moringaProduct->id, 'level' => 2, 'bonus_amount' => 25.00, 'is_active' => true],
                ['product_id' => $moringaProduct->id, 'level' => 3, 'bonus_amount' => 20.00, 'is_active' => true],
                ['product_id' => $moringaProduct->id, 'level' => 4, 'bonus_amount' => 15.00, 'is_active' => true],
                ['product_id' => $moringaProduct->id, 'level' => 5, 'bonus_amount' => 15.00, 'is_active' => true],
            ];

            foreach ($unilevelSettings as $setting) {
                DB::table('unilevel_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Create Unilevel settings for Turmeric Capsules (Total: 100.00)
        if ($turmericProduct) {
            $unilevelSettings = [
                ['product_id' => $turmericProduct->id, 'level' => 1, 'bonus_amount' => 45.00, 'is_active' => true],
                ['product_id' => $turmericProduct->id, 'level' => 2, 'bonus_amount' => 20.00, 'is_active' => true],
                ['product_id' => $turmericProduct->id, 'level' => 3, 'bonus_amount' => 15.00, 'is_active' => true],
                ['product_id' => $turmericProduct->id, 'level' => 4, 'bonus_amount' => 10.00, 'is_active' => true],
                ['product_id' => $turmericProduct->id, 'level' => 5, 'bonus_amount' => 10.00, 'is_active' => true],
            ];

            foreach ($unilevelSettings as $setting) {
                DB::table('unilevel_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Create Unilevel settings for Omega-3 Fish Oil (Total: 150.00)
        if ($omegaProduct) {
            $unilevelSettings = [
                ['product_id' => $omegaProduct->id, 'level' => 1, 'bonus_amount' => 65.00, 'is_active' => true],
                ['product_id' => $omegaProduct->id, 'level' => 2, 'bonus_amount' => 30.00, 'is_active' => true],
                ['product_id' => $omegaProduct->id, 'level' => 3, 'bonus_amount' => 22.00, 'is_active' => true],
                ['product_id' => $omegaProduct->id, 'level' => 4, 'bonus_amount' => 17.00, 'is_active' => true],
                ['product_id' => $omegaProduct->id, 'level' => 5, 'bonus_amount' => 16.00, 'is_active' => true],
            ];

            foreach ($unilevelSettings as $setting) {
                DB::table('unilevel_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Create Unilevel settings for Green Tea Extract (Total: 85.00)
        if ($greenTeaProduct) {
            $unilevelSettings = [
                ['product_id' => $greenTeaProduct->id, 'level' => 1, 'bonus_amount' => 38.00, 'is_active' => true],
                ['product_id' => $greenTeaProduct->id, 'level' => 2, 'bonus_amount' => 17.00, 'is_active' => true],
                ['product_id' => $greenTeaProduct->id, 'level' => 3, 'bonus_amount' => 12.00, 'is_active' => true],
                ['product_id' => $greenTeaProduct->id, 'level' => 4, 'bonus_amount' => 9.00, 'is_active' => true],
                ['product_id' => $greenTeaProduct->id, 'level' => 5, 'bonus_amount' => 9.00, 'is_active' => true],
            ];

            foreach ($unilevelSettings as $setting) {
                DB::table('unilevel_settings')->insert(array_merge($setting, [
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
        // Delete Unilevel settings for the products we're removing
        $products = DB::table('products')
            ->whereIn('slug', ['moringa-powder', 'turmeric-capsules', 'omega-3-fish-oil', 'green-tea-extract'])
            ->get();
        
        foreach ($products as $product) {
            DB::table('unilevel_settings')->where('product_id', $product->id)->delete();
        }

        // Delete the products
        DB::table('products')->whereIn('slug', [
            'moringa-powder',
            'turmeric-capsules',
            'omega-3-fish-oil',
            'green-tea-extract'
        ])->delete();
    }
};
