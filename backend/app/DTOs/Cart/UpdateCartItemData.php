<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Http\Requests\Cart\UpdateCartItemRequest;

final readonly class UpdateCartItemData
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}

    public static function fromRequest(UpdateCartItemRequest $request, int $productId): self
    {
        /** @var array{quantity: int|string} $validated */
        $validated = $request->validated();

        return new self(
            productId: $productId,
            quantity: (int) $validated['quantity'],
        );
    }
}
