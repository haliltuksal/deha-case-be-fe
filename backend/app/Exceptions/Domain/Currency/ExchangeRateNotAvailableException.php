<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Currency;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Raised when the requested currency has no rate in either the cache or
 * the database. The application has not yet completed a successful first
 * fetch, or the requested currency is not in the supported set.
 */
final class ExchangeRateNotAvailableException extends ApiException
{
    public function getStatusCode(): int
    {
        return HttpResponse::HTTP_SERVICE_UNAVAILABLE;
    }

    public function getErrorCode(): string
    {
        return 'ERR_EXCHANGE_RATE_UNAVAILABLE';
    }

    protected function defaultMessage(): string
    {
        return 'No exchange rate is currently available for the requested currency.';
    }
}
