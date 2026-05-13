<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Enums\Currency;
use InvalidArgumentException;

final readonly class CurrencyConverter
{
    private const INTERMEDIATE_SCALE = 8;

    private const FINAL_SCALE = 2;

    public function __construct(
        private ExchangeRateService $rates,
    ) {}

    /**
     * @return numeric-string
     */
    public function convert(string $amount, Currency $from, Currency $to): string
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException(
                "Amount must be a numeric string. Got: {$amount}",
            );
        }

        if ($from === $to) {
            return $this->roundFinal($amount);
        }

        $amountInTry = bcmul(
            $amount,
            $this->rates->getRateInTry($from),
            self::INTERMEDIATE_SCALE,
        );

        if ($to === Currency::TRY) {
            return $this->roundFinal($amountInTry);
        }

        return $this->roundFinal(bcdiv(
            $amountInTry,
            $this->rates->getRateInTry($to),
            self::INTERMEDIATE_SCALE,
        ));
    }

    /**
     * @param numeric-string $value
     *
     * @return numeric-string
     */
    private function roundFinal(string $value): string
    {
        $rounded = number_format((float) $value, self::FINAL_SCALE, '.', '');
        assert(is_numeric($rounded));

        return $rounded;
    }
}
