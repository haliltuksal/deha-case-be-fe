<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\Currency\ExchangeRateData;
use App\Exceptions\Domain\Currency\ExchangeRateProviderException;
use Illuminate\Support\Collection;

interface ExchangeRateProviderInterface
{
    /**
     * Fetch the most recent set of rates from the upstream source.
     *
     * @return Collection<int, ExchangeRateData>
     *
     * @throws ExchangeRateProviderException
     */
    public function fetchRates(): Collection;

    /**
     * Stable identifier used in logs and metrics.
     */
    public function name(): string;
}
