<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\UnilevelSetting;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Collagen Beauty Drink',
                'price' => 1500.00,
                'points_awarded' => 25,
                'short_description' => 'Marine collagen peptides for youthful skin and strong nails',
                'long_description' => 'Transform your beauty routine with our Collagen Beauty Drink. Made from marine collagen peptides with added vitamins C and E, this delicious drink supports skin elasticity, reduces fine lines, and strengthens hair and nails.',
                'quantity_available' => 100,
                'category' => 'Beauty',
                'weight_grams' => 300,
                'unilevel_settings' => [
                    ['level' => 1, 'bonus_amount' => 50.00],
                    ['level' => 2, 'bonus_amount' => 30.00],
                    ['level' => 3, 'bonus_amount' => 20.00],
                    ['level' => 4, 'bonus_amount' => 15.00],
                    ['level' => 5, 'bonus_amount' => 15.00],
                ]
            ],
            [
                'name' => 'Immune Booster Capsules',
                'price' => 1200.00,
                'points_awarded' => 20,
                'short_description' => 'A blend of Vitamin C, Zinc, and Elderberry to support your immune system.',
                'long_description' => 'Stay protected year-round with our Immune Booster Capsules. Each capsule is packed with a powerful combination of Vitamin C, Zinc, and Elderberry extract to help strengthen your body\'s natural defenses.',
                'quantity_available' => 50,
                'category' => 'Health & Wellness',
                'weight_grams' => 150,
                'unilevel_settings' => [
                    ['level' => 1, 'bonus_amount' => 40.00],
                    ['level' => 2, 'bonus_amount' => 25.00],
                    ['level' => 3, 'bonus_amount' => 15.00],
                    ['level' => 4, 'bonus_amount' => 10.00],
                    ['level' => 5, 'bonus_amount' => 10.00],
                ]
            ],
        ];

        foreach ($products as $productData) {
            $unilevelSettings = $productData['unilevel_settings'];
            unset($productData['unilevel_settings']);

            $product = Product::create($productData);
            $product->unilevelSettings()->createMany($unilevelSettings);
            $product->updateTotalUnilevelBonus();
        }
    }
}