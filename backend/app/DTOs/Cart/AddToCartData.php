<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Http\Requests\Cart\AddToCartRequest;

final readonly class AddToCartData
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}

    public static function fromRequest(AddToCartRequest $request): self
    {
        /** @var array{product_id: int|string, quantity: int|string} $validated */
        $validated = $request->validated();

        return new self(
            productId: (int) $validated['product_id'],
            quantity: (int) $validated['quantity'],
        );
    }
}
