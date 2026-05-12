<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

interface CartRepositoryInterface
{
    public function findOrCreateForUser(User $user): Cart;

    public function findItem(Cart $cart, int $productId): ?CartItem;

    public function addOrIncrementItem(Cart $cart, int $productId, int $quantity): CartItem;

    public function setItemQuantity(CartItem $item, int $quantity): CartItem;

    public function removeItem(CartItem $item): void;

    public function clearItems(Cart $cart): void;

    public function loadItemsWithProducts(Cart $cart): Cart;
}
