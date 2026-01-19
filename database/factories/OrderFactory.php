<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $totalAmount = fake()->randomFloat(2, 100, 10000);
        $taxRate = fake()->randomFloat(4, 0, 0.15);
        $taxAmount = $totalAmount * $taxRate;
        $subtotal = $totalAmount - $taxAmount;

        return [
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->lexify('??????')),
            'user_id' => User::factory(),
            'total_amount' => $totalAmount,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'status' => fake()->randomElement(['pending', 'confirmed', 'packing', 'delivery', 'completed', 'cancelled']),
            'delivery_method' => fake()->randomElement(['office_pickup', 'home_delivery']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'refunded']),
            'delivery_address' => json_encode([
                'province' => fake()->word(),
                'city' => fake()->word(),
                'barangay' => fake()->word(),
                'street' => fake()->streetAddress(),
            ]),
            'delivered_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'delivered_at' => fake()->dateTime(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }
}
