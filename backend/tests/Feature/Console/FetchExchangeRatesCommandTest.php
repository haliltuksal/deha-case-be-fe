<?php

declare(strict_types=1);

use App\Contracts\Services\ExchangeRateProviderInterface;
use App\DTOs\Currency\ExchangeRateData;
use App\Enums\Currency;
use App\Exceptions\Domain\Currency\ExchangeRateProviderException;
use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

it('fetches rates, persists them, and exits with a success status', function (): void {
    $this->mock(ExchangeRateProviderInterface::class, function ($mock): void {
        $mock->shouldReceive('fetchRates')->once()->andReturn(new Collection([
            new ExchangeRateData(Currency::USD, '32.5407', CarbonImmutable::now()),
            new ExchangeRateData(Currency::EUR, '34.9203', CarbonImmutable::now()),
        ]));
    });

    $this->artisan('currency:fetch')
        ->expectsOutputToContain('Fetched 2 exchange rate(s).')
        ->assertSuccessful();

    expect(ExchangeRate::query()->count())->toBe(2);
});

it('exits with a failure status when the provider raises ExchangeRateProviderException', function (): void {
    $this->mock(ExchangeRateProviderInterface::class, function ($mock): void {
        $mock->shouldReceive('fetchRates')->once()->andThrow(
            new ExchangeRateProviderException('TCMB unreachable'),
        );
    });

    $this->artisan('currency:fetch')
        ->expectsOutputToContain('Provider failure: TCMB unreachable')
        ->assertFailed();

    expect(ExchangeRate::query()->count())->toBe(0);
});
