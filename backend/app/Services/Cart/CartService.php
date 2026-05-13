<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Actions\Cart\AddToCartAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\DTOs\Cart\AddToCartData;
use App\DTOs\Cart\UpdateCartItemData;
use App\Models\Cart;
use App\Models\User;

final readonly class CartService
{
    public function __construct(
        private GetCartAction $getAction,
        private AddToCartAction $addAction,
        private UpdateCartItemAction $updateAction,
        private RemoveCartItemAction $removeAction,
        private ClearCartAction $clearAction,
    ) {}

    public function get(User $user): Cart
    {
        return $this->getAction->execute($user);
    }

    public function add(User $user, AddToCartData $data): Cart
    {
        return $this->addAction->execute($user, $data);
    }

    public function update(User $user, UpdateCartItemData $data): Cart
    {
        return $this->updateAction->execute($user, $data);
    }

    public function remove(User $user, int $productId): void
    {
        $this->removeAction->execute($user, $productId);
    }

    public function clear(User $user): void
    {
        $this->clearAction->execute($user);
    }
}
