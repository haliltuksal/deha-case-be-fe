<?php

declare(strict_types=1);

use App\Enums\Currency;
use App\Exceptions\Domain\Currency\ExchangeRateProviderException;
use App\Services\Currency\Providers\TcmbExchangeRateProvider;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->fixture = file_get_contents(__DIR__ . '/../../../Fixtures/tcmb-sample.xml');
    $this->url = 'https://www.tcmb.gov.tr/kurlar/today.xml';
    $this->provider = new TcmbExchangeRateProvider($this->url);
});

it('parses TCMB xml and yields the convertible exchange rates', function (): void {
    Http::fake([$this->url => Http::response($this->fixture, 200)]);

    $rates = $this->provider->fetchRates();

    expect($rates)->toHaveCount(2);

    $usd = $rates->firstWhere('currency', Currency::USD);
    expect($usd)->not->toBeNull()
        ->and($usd->rateInTry)->toBe('32.5407');

    $eur = $rates->firstWhere('currency', Currency::EUR);
    expect($eur)->not->toBeNull()
        ->and($eur->rateInTry)->toBe('34.9203');
});

it('ignores non-convertible currencies even when present in the upstream payload', function (): void {
    Http::fake([$this->url => Http::response($this->fixture, 200)]);

    $rates = $this->provider->fetchRates();

    $codes = $rates->map(fn ($rate): string => $rate->currency->value)->all();
    expect($codes)->toEqualCanonicalizing(['USD', 'EUR']);
});

it('throws ExchangeRateProviderException when the upstream returns 5xx', function (): void {
    Http::fake([$this->url => Http::response('', 500)]);

    expect(fn () => $this->provider->fetchRates())
        ->toThrow(ExchangeRateProviderException::class);
});

it('throws ExchangeRateProviderException when the response body is not valid xml', function (): void {
    Http::fake([$this->url => Http::response('this is not xml', 200)]);

    expect(fn () => $this->provider->fetchRates())
        ->toThrow(ExchangeRateProviderException::class);
});

it('throws ExchangeRateProviderException when the convertible currencies are missing', function (): void {
    $stripped = '<?xml version="1.0"?><Tarih_Date><Currency CurrencyCode="GBP"><Unit>1</Unit><ForexSelling>40.59</ForexSelling></Currency></Tarih_Date>';
    Http::fake([$this->url => Http::response($stripped, 200)]);

    expect(fn () => $this->provider->fetchRates())
        ->toThrow(ExchangeRateProviderException::class);
});

it('reports its provider name as tcmb', function (): void {
    expect($this->provider->name())->toBe('tcmb');
});
