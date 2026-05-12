<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTOs\Cart\CachedCart;
use Illuminate\Contracts\Cache\Lock;

/**
 * Contract for the cart-side cache. The default implementation stores the
 * cart snapshot in Redis (write-through) so reads can skip the carts and
 * cart_items tables on a cache hit while writes still hit the database for
 * durability.
 */
interface CartCacheRepositoryInterface
{
    public function get(int $userId): ?CachedCart;

    public function put(CachedCart $cart): void;

    public function forget(int $userId): void;

    /**
     * Acquire a per-user mutex around cart mutations to prevent two
     * concurrent requests from racing each other on the cache key.
     */
    public function lock(int $userId, int $seconds = 5): Lock;
}
