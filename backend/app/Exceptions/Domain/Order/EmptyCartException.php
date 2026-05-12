<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Order;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Raised when an order creation is attempted against an empty cart.
 */
final class EmptyCartException extends ApiException
{
    public function getStatusCode(): int
    {
        return HttpResponse::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrorCode(): string
    {
        return 'ERR_EMPTY_CART';
    }

    protected function defaultMessage(): string
    {
        return 'Cannot create an order from an empty cart.';
    }
}
