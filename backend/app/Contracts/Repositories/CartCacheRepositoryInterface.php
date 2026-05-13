<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTOs\Cart\CachedCart;
use Illuminate\Contracts\Cache\Lock;

interface CartCacheRepositoryInterface
{
    public function get(int $userId): ?CachedCart;

    public function put(CachedCart $cart): void;

    public function forget(int $userId): void;

    public function lock(int $userId, int $seconds = 5): Lock;
}
