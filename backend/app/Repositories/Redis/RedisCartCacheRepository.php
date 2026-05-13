<?php

declare(strict_types=1);

namespace App\Repositories\Redis;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\DTOs\Cart\CachedCart;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

final readonly class RedisCartCacheRepository implements CartCacheRepositoryInterface
{
    private const TTL_SECONDS = 60 * 60 * 24 * 30; // 30 days

    public function __construct(
        private CacheRepository $cache,
    ) {}

    public function get(int $userId): ?CachedCart
    {
        $payload = $this->cache->get($this->key($userId));
        if (! is_array($payload)) {
            return null;
        }

        return CachedCart::fromArray($payload);
    }

    public function put(CachedCart $cart): void
    {
        $this->cache->put($this->key($cart->userId), $cart->toArray(), self::TTL_SECONDS);
    }

    public function forget(int $userId): void
    {
        $this->cache->forget($this->key($userId));
    }

    public function lock(int $userId, int $seconds = 5): Lock
    {
        return Cache::lock($this->lockKey($userId), $seconds);
    }

    private function key(int $userId): string
    {
        return "cart:user:{$userId}";
    }

    private function lockKey(int $userId): string
    {
        return "cart:user:{$userId}:lock";
    }
}
