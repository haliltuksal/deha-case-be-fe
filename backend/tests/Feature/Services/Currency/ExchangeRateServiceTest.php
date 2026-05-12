<?php

declare(strict_types=1);

use App\Contracts\Repositories\ExchangeRateRepositoryInterface;
use App\Contracts\Services\ExchangeRateProviderInterface;
use App\DTOs\Currency\ExchangeRateData;
use App\Enums\Currency;
use App\Exceptions\Domain\Currency\ExchangeRateNotAvailableException;
use App\Models\ExchangeRate;
use App\Services\Currency\ExchangeRateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
});

function makeService(ExchangeRateProviderInterface $provider): ExchangeRateService
{
    return new ExchangeRateService(
        provider: $provider,
        repository: app(ExchangeRateRepositoryInterface::class),
        cache: Cache::store(),
        cacheTtl: 3600,
    );
}

it('returns 1 for the TRY currency without touching cache or storage', function (): void {
    $provider = Mockery::mock(ExchangeRateProviderInterface::class);
    $provider->shouldNotReceive('fetchRates');

    $service = makeService($provider);

    expect($service->getRateInTry(Currency::TRY))->toBe('1');
});

it('returns the cached rate when one is present', function (): void {
    $provider = Mockery::mock(ExchangeRateProviderInterface::class);
    $service = makeService($provider);

    Cache::put('exchange_rate:USD', '32.5407', 3600);

    expect($service->getRateInTry(Currency::USD))->toBe('32.5407');
});

it('falls back to the database when the cache has no entry', function (): void {
    $provider = Mockery::mock(ExchangeRateProviderInterface::class);
    $service = makeService($provider);

    ExchangeRate::factory()->usd()->create(['rate_in_try' => '40.10000000']);

    $rate = $service->getRateInTry(Currency::USD);

    expect($rate)->toBe('40.10000000')
        ->and(Cache::get('exchange_rate:USD'))->toBe('40.10000000');
});

it('throws ExchangeRateNotAvailableException when nothing is cached or persisted', function (): void {
    $provider = Mockery::mock(ExchangeRateProviderInterface::class);
    $service = makeService($provider);

    expect(fn () => $service->getRateInTry(Currency::EUR))
        ->toThrow(ExchangeRateNotAvailableException::class);
});

it('persists fetched rates and warms the cache', function (): void {
    $provider = Mockery::mock(ExchangeRateProviderInterface::class);
    $provider->shouldReceive('fetchRates')->once()->andReturn(new Collection([
        new ExchangeRateData(Currency::USD, '32.5407', CarbonImmutable::now()),
        new ExchangeRateData(Currency::EUR, '34.9203', CarbonImmutable::now()),
    ]));

    $service = makeService($provider);
    $rates = $service->fetchAndStoreLatest();

    expect($rates)->toHaveCount(2)
        ->and(ExchangeRate::query()->count())->toBe(2)
        ->and(Cache::get('exchange_rate:USD'))->toBe('32.5407')
        ->and(Cache::get('exchange_rate:EUR'))->toBe('34.9203');
});
