<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Cart;

use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
