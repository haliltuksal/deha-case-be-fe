<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

final class CartRepository implements CartRepositoryInterface
{
    public function findOrCreateForUser(User $user): Cart
    {
        /** @var Cart $cart */
        $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);

        return $cart;
    }

    public function findItem(Cart $cart, int $productId): ?CartItem
    {
        /** @var CartItem|null $item */
        $item = $cart->items()->where('product_id', $productId)->first();

        return $item;
    }

    public function addOrIncrementItem(Cart $cart, int $productId, int $quantity): CartItem
    {
        /** @var CartItem $item */
        $item = $cart->items()->firstOrNew(['product_id' => $productId]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->save();

        return $item;
    }

    public function setItemQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearItems(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function loadItemsWithProducts(Cart $cart): Cart
    {
        return $cart->load(['items.product']);
    }
}
