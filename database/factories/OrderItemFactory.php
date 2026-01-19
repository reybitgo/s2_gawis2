<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $itemType = fake()->randomElement(['package', 'product']);

        return [
            'order_id' => Order::factory(),
            'package_id' => $itemType === 'package' ? Package::factory() : null,
            'product_id' => $itemType === 'product' ? Product::factory() : null,
            'item_type' => $itemType,
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 100, 5000),
            'total_price' => 0,
            'points_awarded_per_item' => fake()->numberBetween(0, 100),
            'total_points_awarded' => 0,
            'package_snapshot' => null,
            'product_snapshot' => null,
        ];
    }

    public function package(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'package',
            'product_id' => null,
        ]);
    }

    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'product',
            'package_id' => null,
        ]);
    }
}
