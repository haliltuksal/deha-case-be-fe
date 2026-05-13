<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\ExchangeRateRepositoryInterface;
use App\Contracts\Services\ExchangeRateProviderInterface;
use App\Services\Currency\CurrencyConverter;
use App\Services\Currency\ExchangeRateService;
use App\Services\Currency\Providers\TcmbExchangeRateProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->bindJwtGuard();
        $this->bindCurrencyServices();
    }

    public function boot(): void
    {
        $this->assertProductionSafety();
        $this->configureRateLimiters();
    }

    private function assertProductionSafety(): void
    {
        if ($this->app->environment('production') && config('app.debug') === true) {
            throw new \RuntimeException(
                'APP_DEBUG must be false in production. Refusing to boot with debug enabled.',
            );
        }
    }

    private function bindJwtGuard(): void
    {
        $this->app->bind(JWTGuard::class, function ($app): JWTGuard {
            /** @var AuthFactory $auth */
            $auth = $app->make(AuthFactory::class);
            $guard = $auth->guard('api');
            assert($guard instanceof JWTGuard);

            return $guard;
        });
    }

    private function bindCurrencyServices(): void
    {
        $this->app->bind(ExchangeRateProviderInterface::class, function ($app): TcmbExchangeRateProvider {
            $url = config('services.tcmb.url');
            assert(is_string($url) && $url !== '');

            return new TcmbExchangeRateProvider(url: $url);
        });

        $this->app->singleton(ExchangeRateService::class, function ($app): ExchangeRateService {
            $ttl = (int) config('services.currency.cache_ttl', 86400);

            /** @var CacheRepository $cache */
            $cache = Cache::store();

            return new ExchangeRateService(
                provider: $app->make(ExchangeRateProviderInterface::class),
                repository: $app->make(ExchangeRateRepositoryInterface::class),
                cacheTtl: $ttl,
                cache: $cache,
            );
        });

        $this->app->singleton(CurrencyConverter::class);
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            $key = $request->user()?->getAuthIdentifier();
            assert($key === null || is_int($key) || is_string($key));

            return Limit::perMinute(60)->by((string) ($key ?? $request->ip()));
        });

        RateLimiter::for('auth', static function (Request $request): Limit {
            return Limit::perMinute(5)->by((string) $request->ip());
        });
    }
}
