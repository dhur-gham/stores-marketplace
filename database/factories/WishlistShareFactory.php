<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WishlistShare>
 */
class WishlistShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'share_token' => \App\Models\WishlistShare::generate_token(),
            'custom_message' => null,
            'is_active' => true,
            'views_count' => 0,
        ];
    }
}
