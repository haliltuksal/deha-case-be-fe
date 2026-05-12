<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Cart\CachedCart;
use App\DTOs\Cart\UpdateCartItemData;
use App\Exceptions\Domain\Cart\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCartItemAction
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private ProductRepositoryInterface $products,
        private CartCacheRepositoryInterface $cache,
    ) {}

    /**
     * @throws InsufficientStockException
     * @throws ModelNotFoundException when the cart has no item for the supplied product
     */
    public function execute(User $user, UpdateCartItemData $data): Cart
    {
        $lock = $this->cache->lock($user->id);

        return $lock->block(5, fn (): Cart => DB::transaction(function () use ($user, $data): Cart {
            $cart = $this->carts->findOrCreateForUser($user);
            $item = $this->carts->findItem($cart, $data->productId);

            if ($item === null) {
                throw (new ModelNotFoundException)->setModel(CartItem::class);
            }

            $product = $this->products->findOrFail($data->productId);

            if ($data->quantity > $product->stock) {
                throw InsufficientStockException::for(
                    productId: $product->id,
                    requested: $data->quantity,
                    available: $product->stock,
                );
            }

            $this->carts->setItemQuantity($item, $data->quantity);

            $cart = $this->carts->loadItemsWithProducts($cart);

            DB::afterCommit(fn () => $this->cache->put(CachedCart::fromCart($cart)));

            return $cart;
        }));
    }
}
