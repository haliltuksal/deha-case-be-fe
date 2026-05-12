<?php

declare(strict_types=1);

use App\Enums\Currency;
use App\Models\ExchangeRate;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    Cache::put('exchange_rate:USD', '32.5407', 3600);
    Cache::put('exchange_rate:EUR', '34.9203', 3600);
});

it('returns the same amount when source and target currencies match', function (): void {
    $converter = app(CurrencyConverter::class);

    expect($converter->convert('123.45', Currency::TRY, Currency::TRY))->toBe('123.45');
    expect($converter->convert('99.00', Currency::USD, Currency::USD))->toBe('99.00');
});

it('converts TRY into USD using the cached rate', function (): void {
    $converter = app(CurrencyConverter::class);

    // 100 TRY / 32.5407 USD-per-TRY = 3.0731... → 3.07
    expect($converter->convert('100', Currency::TRY, Currency::USD))->toBe('3.07');
});

it('converts USD into TRY using the cached rate', function (): void {
    $converter = app(CurrencyConverter::class);

    // 100 USD * 32.5407 = 3254.07
    expect($converter->convert('100', Currency::USD, Currency::TRY))->toBe('3254.07');
});

it('converts USD into EUR by pivoting through TRY', function (): void {
    $converter = app(CurrencyConverter::class);

    // (100 USD * 32.5407) / 34.9203 = 93.18...
    $result = $converter->convert('100', Currency::USD, Currency::EUR);
    expect((float) $result)->toBeGreaterThan(93.10)->toBeLessThan(93.30);
});

it('rounds the final amount to two decimal places', function (): void {
    $converter = app(CurrencyConverter::class);

    $value = $converter->convert('1', Currency::TRY, Currency::USD);

    expect($value)->toMatch('/^\d+\.\d{2}$/');
});

it('uses persisted rates when the cache is cold', function (): void {
    Cache::flush();
    ExchangeRate::factory()->usd()->create(['rate_in_try' => '32.5407']);

    $converter = app(CurrencyConverter::class);

    expect($converter->convert('100', Currency::USD, Currency::TRY))->toBe('3254.07');
});
