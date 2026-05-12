<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTOs\Currency\ExchangeRateData;
use App\Enums\Currency;
use App\Models\ExchangeRate;

interface ExchangeRateRepositoryInterface
{
    public function latestForCurrency(Currency $currency): ?ExchangeRate;

    public function upsert(ExchangeRateData $data): ExchangeRate;
}
