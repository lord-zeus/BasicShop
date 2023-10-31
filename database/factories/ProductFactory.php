<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        return [
            'name' => fake()->text,
            'sku' => fake()->unique()->name,
            'slug' => Str::slug(fake()->unique()->text),
            'price' => fake()->numberBetween(100, 10000),
            'image' => '/public/images/ZGo6ia20WYlUEAQ0GSbV8G3nO3tcADPUOcb2g907.png',
        ];
    }
}
