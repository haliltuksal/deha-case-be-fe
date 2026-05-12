<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(12),
            'price' => fake()->randomFloat(2, 10, 1000),
            'base_currency' => Currency::TRY->value,
            'stock' => fake()->numberBetween(0, 100),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (): array => [
            'stock' => 0,
        ]);
    }

    public function inUsd(): static
    {
        return $this->state(fn (): array => [
            'base_currency' => Currency::USD->value,
            'price' => fake()->randomFloat(2, 1, 100),
        ]);
    }

    public function inEur(): static
    {
        return $this->state(fn (): array => [
            'base_currency' => Currency::EUR->value,
            'price' => fake()->randomFloat(2, 1, 100),
        ]);
    }
}
