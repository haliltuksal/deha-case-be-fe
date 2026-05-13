<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\User;

final readonly class ClearCartAction
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private CartCacheRepositoryInterface $cache,
    ) {}

    public function execute(User $user): void
    {
        $cart = $this->carts->findOrCreateForUser($user);
        $this->carts->clearItems($cart);
        $this->cache->forget($user->id);
    }
}
