<?php

declare(strict_types=1);

namespace App\Services\Currency\Providers;

use App\Contracts\Services\ExchangeRateProviderInterface;
use App\DTOs\Currency\ExchangeRateData;
use App\Enums\Currency;
use App\Exceptions\Domain\Currency\ExchangeRateProviderException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Throwable;

final class TcmbExchangeRateProvider implements ExchangeRateProviderInterface
{
    private const TIMEOUT_SECONDS = 10;

    public function __construct(
        private readonly string $url,
    ) {}

    public function fetchRates(): Collection
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->acceptJson()
                ->get($this->url)
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            throw new ExchangeRateProviderException(
                "TCMB request failed: {$e->getMessage()}",
                previous: $e,
            );
        }

        return $this->parse($response->body());
    }

    public function name(): string
    {
        return 'tcmb';
    }

    /**
     * @return Collection<int, ExchangeRateData>
     */
    private function parse(string $xml): Collection
    {
        try {
            libxml_use_internal_errors(true);
            $document = simplexml_load_string($xml);
        } catch (Throwable $e) {
            throw new ExchangeRateProviderException(
                "TCMB response is not valid XML: {$e->getMessage()}",
                previous: $e,
            );
        }

        if (! $document instanceof SimpleXMLElement) {
            throw new ExchangeRateProviderException('TCMB response is not valid XML.');
        }

        $fetchedAt = $this->parseFetchedAt($document);
        $convertibles = collect(Currency::convertibles())
            ->map(fn (Currency $currency): string => $currency->value)
            ->all();

        $rates = new Collection;

        foreach ($document->Currency as $node) {
            $code = (string) $node['CurrencyCode'];

            if (! in_array($code, $convertibles, true)) {
                continue;
            }

            $unit = (int) ($node->Unit ?? 1);
            $forexSelling = trim((string) ($node->ForexSelling ?? ''));

            if ($forexSelling === '' || $unit < 1) {
                throw new ExchangeRateProviderException(
                    "TCMB payload is missing ForexSelling for {$code}.",
                );
            }

            $rates->push(new ExchangeRateData(
                currency: Currency::from($code),
                rateInTry: $unit === 1 ? $forexSelling : bcdiv($forexSelling, (string) $unit, 8),
                fetchedAt: $fetchedAt,
            ));
        }

        if ($rates->isEmpty()) {
            throw new ExchangeRateProviderException('TCMB payload contained no convertible currencies.');
        }

        return $rates;
    }

    private function parseFetchedAt(SimpleXMLElement $document): CarbonImmutable
    {
        $tarih = (string) ($document['Tarih'] ?? '');

        if ($tarih === '') {
            return CarbonImmutable::now();
        }

        try {
            return CarbonImmutable::createFromFormat('!d.m.Y', $tarih) ?: CarbonImmutable::now();
        } catch (Throwable) {
            return CarbonImmutable::now();
        }
    }
}
