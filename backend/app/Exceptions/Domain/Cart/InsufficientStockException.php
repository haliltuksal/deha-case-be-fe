<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Cart;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Raised when a cart operation would push a line item past the
 * referenced product's available stock. The `details` payload exposes
 * the offending product and the requested vs. available counts so the
 * frontend can render an inline error.
 */
final class InsufficientStockException extends ApiException
{
    public static function for(int $productId, int $requested, int $available): self
    {
        return new self(
            message: 'Requested quantity exceeds the available stock.',
            details: [
                'product_id' => $productId,
                'requested' => $requested,
                'available' => $available,
            ],
        );
    }

    public function getStatusCode(): int
    {
        return HttpResponse::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrorCode(): string
    {
        return 'ERR_INSUFFICIENT_STOCK';
    }
}
