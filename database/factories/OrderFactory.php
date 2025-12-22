<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $delivery_price = fake()->numberBetween(3000, 15000);
        $items_total = fake()->numberBetween(10000, 500000);

        return [
            'user_id' => \App\Models\User::factory(),
            'store_id' => \App\Models\Store::factory(),
            'city_id' => \App\Models\City::factory(),
            'total' => $items_total + $delivery_price,
            'delivery_price' => $delivery_price,
            'status' => fake()->randomElement(['new', 'pending', 'processing', 'completed', 'cancelled', 'refunded']),
        ];
    }
}
