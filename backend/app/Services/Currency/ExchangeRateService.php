<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Contracts\Repositories\ExchangeRateRepositoryInterface;
use App\Contracts\Services\ExchangeRateProviderInterface;
use App\DTOs\Currency\ExchangeRateData;
use App\Enums\Currency;
use App\Exceptions\Domain\Currency\ExchangeRateNotAvailableException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-through cache around the configured ExchangeRateProvider plus the
 * persistence layer. Callers ask for rates by currency without caring
 * whether the value came from cache, the database, or a fresh fetch.
 */
final class ExchangeRateService
{
    /**
     * Per-request memo for resolved rates. The Laravel container resolves
     * this service as a singleton, so this map acts as an in-process cache
     * that lasts for one request — saving the Redis round-trip on the second
     * and subsequent currency lookups within the same response.
     *
     * @var array<string, numeric-string>
     */
    private array $memo = [];

    public function __construct(
        private readonly ExchangeRateProviderInterface $provider,
        private readonly ExchangeRateRepositoryInterface $repository,
        private readonly CacheRepository $cache,
        private readonly int $cacheTtl,
    ) {}

    /**
     * Fetch the latest rates from the upstream provider, persist them, and
     * warm the cache. Used by the daily scheduled command and by manual
     * refresh actions.
     *
     * @return Collection<int, ExchangeRateData>
     */
    public function fetchAndStoreLatest(): Collection
    {
        $rates = $this->provider->fetchRates();

        DB::transaction(function () use ($rates): void {
            foreach ($rates as $rate) {
                $this->repository->upsert($rate);
            }
        });

        foreach ($rates as $rate) {
            $this->cache->put($this->cacheKey($rate->currency), $rate->rateInTry, $this->cacheTtl);
        }

        return $rates;
    }

    /**
     * How many TRY one unit of the supplied currency is worth.
     *
     * @return numeric-string
     *
     * @throws ExchangeRateNotAvailableException
     */
    public function getRateInTry(Currency $currency): string
    {
        if ($currency === Currency::TRY) {
            return '1';
        }

        if (isset($this->memo[$currency->value])) {
            return $this->memo[$currency->value];
        }

        $cached = $this->cache->get($this->cacheKey($currency));
        if (is_string($cached) && is_numeric($cached)) {
            $this->memo[$currency->value] = $cached;

            return $cached;
        }

        $persisted = $this->repository->latestForCurrency($currency);
        if ($persisted === null) {
            throw new ExchangeRateNotAvailableException(
                "No persisted rate found for {$currency->value}.",
            );
        }

        $rate = $persisted->rate_in_try;
        if (! is_numeric($rate)) {
            throw new ExchangeRateNotAvailableException(
                "Persisted rate for {$currency->value} is malformed.",
            );
        }

        $this->cache->put($this->cacheKey($currency), $rate, $this->cacheTtl);
        $this->memo[$currency->value] = $rate;

        return $rate;
    }

    private function cacheKey(Currency $currency): string
    {
        return "exchange_rate:{$currency->value}";
    }
}
