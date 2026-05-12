<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\ExchangeRateRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\ExchangeRateRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Redis\RedisCartCacheRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository contracts to their Eloquent implementations so the rest
 * of the application can depend on interfaces (Dependency Inversion).
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        ExchangeRateRepositoryInterface::class => ExchangeRateRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        CartRepositoryInterface::class => CartRepository::class,
        CartCacheRepositoryInterface::class => RedisCartCacheRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
    ];

    public function register(): void
    {
        // Bindings are declared via the $bindings array above; reserved for
        // any contract that needs conditional or factory-driven resolution.
    }

    public function boot(): void
    {
        //
    }
}
