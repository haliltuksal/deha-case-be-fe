<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Currency;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Raised when an upstream exchange-rate provider (e.g. TCMB) fails to
 * deliver a usable response — network errors, non-2xx status, malformed
 * payload, or empty result set.
 */
final class ExchangeRateProviderException extends ApiException
{
    public function getStatusCode(): int
    {
        return HttpResponse::HTTP_BAD_GATEWAY;
    }

    public function getErrorCode(): string
    {
        return 'ERR_EXCHANGE_PROVIDER_FAILED';
    }

    protected function defaultMessage(): string
    {
        return 'The upstream exchange-rate provider is currently unreachable.';
    }
}
