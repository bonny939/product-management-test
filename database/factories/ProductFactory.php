<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 1000);
        $price = $this->faker->randomFloat(2, 0.99, 999.99);

        return [
            'name' => $this->faker->unique()->words(3, true),
            'quantity' => $quantity,
            'price' => $price,
            'total_value' => $quantity * $price,
        ];
    }

    /**
     * Indicate that the product is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn(array $attributes) => [
            'deleted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}