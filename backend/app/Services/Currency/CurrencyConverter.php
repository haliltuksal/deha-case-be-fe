<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Enums\Currency;
use InvalidArgumentException;

/**
 * Converts a monetary `amount` between currencies using TRY as the pivot.
 *
 * Math is done with bcmath so we never lose cents. Intermediate steps run
 * at 8-digit precision to absorb rounding noise; the final value is
 * rounded to 2 decimals (cent precision).
 */
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

        // amount-in-TRY = amount * (1 unit of `from` in TRY)
        $amountInTry = bcmul(
            $amount,
            $this->rates->getRateInTry($from),
            self::INTERMEDIATE_SCALE,
        );

        if ($to === Currency::TRY) {
            return $this->roundFinal($amountInTry);
        }

        // amount-in-`to` = amount-in-TRY / (1 unit of `to` in TRY)
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
