<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'price' => fake()->randomFloat(2, 100, 10000),
            'points_awarded' => fake()->numberBetween(10, 500),
            'quantity_available' => fake()->numberBetween(10, 1000),
            'short_description' => fake()->sentence(),
            'long_description' => fake()->paragraphs(3, true),
            'image_path' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'meta_data' => [],
            'is_mlm_package' => fake()->boolean(),
            'max_mlm_levels' => fake()->numberBetween(1, 10),
            'monthly_quota_points' => fake()->randomFloat(2, 50, 500),
            'enforce_monthly_quota' => fake()->boolean(),
            'rank_name' => fake()->word(),
            'rank_order' => fake()->numberBetween(1, 10),
            'required_direct_sponsors' => fake()->numberBetween(0, 10),
            'required_sponsors_ppv_gpv' => fake()->numberBetween(0, 10),
            'ppv_required' => fake()->randomFloat(2, 0, 5000),
            'gpv_required' => fake()->randomFloat(2, 0, 50000),
            'rank_pv_enabled' => fake()->boolean(),
            'is_rankable' => fake()->boolean(),
            'next_rank_package_id' => null,
            'rank_reward' => fake()->randomFloat(2, 0, 1000),
        ];
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Starter Package',
            'slug' => 'starter-package',
            'rank_name' => 'Starter',
            'rank_order' => 1,
            'price' => 1000.00,
            'required_direct_sponsors' => 5,
            'is_mlm_package' => true,
            'is_rankable' => true,
            'max_mlm_levels' => 5,
            'points_awarded' => 100,
        ]);
    }

    public function newbie(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Newbie Package',
            'slug' => 'newbie-package',
            'rank_name' => 'Newbie',
            'rank_order' => 2,
            'price' => 2500.00,
            'required_direct_sponsors' => 8,
            'is_mlm_package' => true,
            'is_rankable' => true,
            'max_mlm_levels' => 5,
            'points_awarded' => 250,
        ]);
    }

    public function bronze(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bronze Package',
            'slug' => 'bronze-package',
            'rank_name' => 'Bronze',
            'rank_order' => 3,
            'price' => 5000.00,
            'required_direct_sponsors' => 10,
            'is_mlm_package' => true,
            'is_rankable' => true,
            'max_mlm_levels' => 5,
            'points_awarded' => 500,
        ]);
    }

    public function mlm(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mlm_package' => true,
            'is_rankable' => true,
        ]);
    }
}
