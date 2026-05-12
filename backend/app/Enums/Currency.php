<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case TRY = 'TRY';
    case USD = 'USD';
    case EUR = 'EUR';

    /**
     * Currencies that need a TCMB-backed exchange rate. TRY is implicit
     * (rate-in-try is always 1) and excluded from persistence.
     *
     * @return list<self>
     */
    public static function convertibles(): array
    {
        return [self::USD, self::EUR];
    }
}
