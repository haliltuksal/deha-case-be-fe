<?php

declare(strict_types=1);

namespace App\DTOs\Currency;

use App\Enums\Currency;
use Carbon\CarbonImmutable;

final readonly class ExchangeRateData
{
    public function __construct(
        public Currency $currency,
        public string $rateInTry,
        public CarbonImmutable $fetchedAt,
    ) {}
}
