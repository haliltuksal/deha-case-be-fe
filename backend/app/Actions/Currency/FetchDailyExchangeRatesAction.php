<?php

declare(strict_types=1);

namespace App\Actions\Currency;

use App\DTOs\Currency\ExchangeRateData;
use App\Services\Currency\ExchangeRateService;
use Illuminate\Support\Collection;

/**
 * Pulls the latest exchange rates and warms the cache + database. Invoked
 * by the scheduled `currency:fetch` command and reusable from any future
 * admin endpoint that needs a manual refresh.
 */
final readonly class FetchDailyExchangeRatesAction
{
    public function __construct(
        private ExchangeRateService $service,
    ) {}

    /**
     * @return Collection<int, ExchangeRateData>
     */
    public function execute(): Collection
    {
        return $this->service->fetchAndStoreLatest();
    }
}
