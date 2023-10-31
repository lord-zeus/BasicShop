<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct>
 */
class OrderProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => fake()->numberBetween(1, 100),
            'order_id' => fake()->numberBetween(1, 20),
            'quantity' => fake()->numberBetween(1, 10),
            'amount' => fake()->numberBetween(100, 1000),
        ];
    }

    public function orderId($id)
    {
        return $this->state([
            'order_id' => $id,
        ]);
    }
}
