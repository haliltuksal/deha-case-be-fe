<?php

declare(strict_types=1);

namespace App\DTOs\Currency;

use App\Enums\Currency;
use Carbon\CarbonImmutable;

/**
 * Snapshot of how many TRY one unit of `currency` is worth, captured at
 * `fetchedAt`. Stored verbatim by the repository and cached by the service.
 */
final readonly class ExchangeRateData
{
    public function __construct(
        public Currency $currency,
        public string $rateInTry,
        public CarbonImmutable $fetchedAt,
    ) {}
}
