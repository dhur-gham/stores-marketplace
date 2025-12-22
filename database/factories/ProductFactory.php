<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'store_id' => \App\Models\Store::factory(),
            'user_id' => \App\Models\User::factory(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'image' => fake()->imageUrl(640, 480, 'products'),
            'description' => fake()->paragraph(),
            'sku' => fake()->unique()->bothify('SKU-####-????'),
            'status' => fake()->randomElement(['active', 'inactive', 'draft']),
            'type' => fake()->randomElement(['digital', 'physical']),
            'price' => fake()->numberBetween(1000, 500000),
            'stock' => fake()->numberBetween(0, 100),
        ];
    }
}
