<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Models\Cart;

/**
 * Wire-format snapshot of a cart kept in the Redis cache. Stores only the
 * minimum needed to reconstruct the cart state for display (item product
 * ids and quantities); product prices and stock are intentionally excluded
 * so they are always fetched fresh from the database when serialised, even
 * on cache hits.
 *
 * @phpstan-type CachedItemArray array{product_id: int, quantity: int}
 * @phpstan-type CachedCartArray array{cart_id: int, user_id: int, items: list<CachedItemArray>, cached_at: string}
 */
final readonly class CachedCart
{
    /**
     * @param list<CachedItemArray> $items
     */
    public function __construct(
        public int $cartId,
        public int $userId,
        public array $items,
        public string $cachedAt,
    ) {}

    public static function fromCart(Cart $cart): self
    {
        /** @var list<CachedItemArray> $items */
        $items = array_values(
            $cart->items
                ->map(static fn ($item): array => [
                    'product_id' => (int) $item->product_id,
                    'quantity' => (int) $item->quantity,
                ])
                ->all(),
        );

        return new self(
            cartId: (int) $cart->id,
            userId: (int) $cart->user_id,
            items: $items,
            cachedAt: now()->toIso8601String(),
        );
    }

    /**
     * @return CachedCartArray
     */
    public function toArray(): array
    {
        return [
            'cart_id' => $this->cartId,
            'user_id' => $this->userId,
            'items' => $this->items,
            'cached_at' => $this->cachedAt,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ?self
    {
        if (! isset($data['cart_id'], $data['user_id'], $data['items']) || ! is_array($data['items'])) {
            return null;
        }

        $items = [];
        foreach ($data['items'] as $row) {
            if (! is_array($row) || ! isset($row['product_id'], $row['quantity'])) {
                return null;
            }
            $items[] = [
                'product_id' => (int) $row['product_id'],
                'quantity' => (int) $row['quantity'],
            ];
        }

        return new self(
            cartId: (int) $data['cart_id'],
            userId: (int) $data['user_id'],
            items: $items,
            cachedAt: isset($data['cached_at']) && is_string($data['cached_at']) ? $data['cached_at'] : '',
        );
    }
}
