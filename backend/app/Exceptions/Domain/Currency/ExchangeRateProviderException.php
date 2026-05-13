<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Currency;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
