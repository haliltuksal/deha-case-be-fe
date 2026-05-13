<?php

declare(strict_types=1);

namespace App\Actions\Currency;

use App\DTOs\Currency\ExchangeRateData;
use App\Services\Currency\ExchangeRateService;
use Illuminate\Support\Collection;

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
