<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\DTOs\Cart\CachedCart;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class RemoveCartItemAction
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private CartCacheRepositoryInterface $cache,
    ) {}

    /**
     * @throws ModelNotFoundException when the cart has no item for the supplied product
     */
    public function execute(User $user, int $productId): void
    {
        $lock = $this->cache->lock($user->id);

        $lock->block(5, fn () => DB::transaction(function () use ($user, $productId): void {
            $cart = $this->carts->findOrCreateForUser($user);
            $item = $this->carts->findItem($cart, $productId);

            if ($item === null) {
                throw (new ModelNotFoundException)->setModel(CartItem::class);
            }

            $this->carts->removeItem($item);

            $cart = $this->carts->loadItemsWithProducts($cart);

            DB::afterCommit(fn () => $this->cache->put(CachedCart::fromCart($cart)));
        }));
    }
}
