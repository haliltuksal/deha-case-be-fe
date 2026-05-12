<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\DTOs\Cart\CachedCart;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-through cart fetch. On a cache hit we rebuild the cart aggregate
 * from the snapshot plus a single bulk product lookup, sparing the
 * `carts` and `cart_items` tables. On a miss we fall back to the database
 * and populate the cache so the next read is served from Redis.
 */
final readonly class GetCartAction
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private CartCacheRepositoryInterface $cache,
    ) {}

    public function execute(User $user): Cart
    {
        $cached = $this->cache->get($user->id);
        if ($cached !== null) {
            $hydrated = $this->hydrateFromCache($user, $cached);
            if ($hydrated !== null) {
                return $hydrated;
            }
            // Cache is stale (cart row missing or product gone) — drop it.
            $this->cache->forget($user->id);
        }

        $cart = $this->carts->findOrCreateForUser($user);
        $cart = $this->carts->loadItemsWithProducts($cart);
        $this->cache->put(CachedCart::fromCart($cart));

        return $cart;
    }

    private function hydrateFromCache(User $user, CachedCart $cached): ?Cart
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()->where('user_id', $user->id)->first();
        if ($cart === null || $cart->id !== $cached->cartId) {
            return null;
        }

        if ($cached->items === []) {
            $cart->setRelation('items', new Collection);

            return $cart;
        }

        $productIds = array_map(static fn (array $row): int => $row['product_id'], $cached->items);
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = new Collection;
        foreach ($cached->items as $row) {
            $product = $products->get($row['product_id']);
            if (! $product instanceof Product) {
                // Cached item points at a deleted product; treat the snapshot
                // as stale rather than serving a phantom line.
                return null;
            }

            $item = new CartItem([
                'cart_id' => $cart->id,
                'product_id' => $row['product_id'],
                'quantity' => $row['quantity'],
            ]);
            $item->exists = true;
            $item->setRelation('product', $product);
            $items->push($item);
        }

        $cart->setRelation('items', $items);

        return $cart;
    }
}
