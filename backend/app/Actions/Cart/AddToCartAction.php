<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Cart\AddToCartData;
use App\DTOs\Cart\CachedCart;
use App\Exceptions\Domain\Cart\InsufficientStockException;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class AddToCartAction
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private ProductRepositoryInterface $products,
        private CartCacheRepositoryInterface $cache,
    ) {}

    /**
     * @throws InsufficientStockException
     */
    public function execute(User $user, AddToCartData $data): Cart
    {
        return DB::transaction(function () use ($user, $data): Cart {
            $product = $this->products->findOrFail($data->productId);
            $cart = $this->carts->findOrCreateForUser($user);
            $existing = $this->carts->findItem($cart, $product->id);
            $existingQuantity = $existing === null ? 0 : $existing->quantity;
            $projectedQuantity = $existingQuantity + $data->quantity;

            if ($projectedQuantity > $product->stock) {
                throw InsufficientStockException::for(
                    productId: $product->id,
                    requested: $projectedQuantity,
                    available: $product->stock,
                );
            }

            $this->carts->addOrIncrementItem($cart, $product->id, $data->quantity);

            $cart = $this->carts->loadItemsWithProducts($cart);

            DB::afterCommit(fn () => $this->cache->put(CachedCart::fromCart($cart)));

            return $cart;
        });
    }
}
