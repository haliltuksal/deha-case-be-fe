<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
final class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'target_currency' => Currency::USD->value,
            'rate_in_try' => '32.50000000',
            'fetched_at' => now(),
        ];
    }

    public function usd(): static
    {
        return $this->state(fn (): array => [
            'target_currency' => Currency::USD->value,
            'rate_in_try' => '32.50000000',
        ]);
    }

    public function eur(): static
    {
        return $this->state(fn (): array => [
            'target_currency' => Currency::EUR->value,
            'rate_in_try' => '35.00000000',
        ]);
    }
}
