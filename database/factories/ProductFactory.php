<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'price' => fake()->randomFloat(2, 100, 5000),
            'points_awarded' => fake()->randomFloat(2, 10, 100),
            'quantity_available' => fake()->numberBetween(10, 1000),
            'short_description' => fake()->sentence(),
            'long_description' => fake()->paragraphs(3, true),
            'image_path' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'meta_data' => [],
            'total_unilevel_bonus' => fake()->randomFloat(2, 0, 500),
            'sku' => 'PROD-' . strtoupper(fake()->randomAscii()),
            'category' => fake()->word(),
            'weight_grams' => fake()->numberBetween(50, 1000),
        ];
    }
}
