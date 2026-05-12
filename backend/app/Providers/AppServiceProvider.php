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
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindJwtGuard();
        $this->bindCurrencyServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    /**
     * Resolve the api guard (JWT) whenever a class type-hints JWTGuard, so
     * actions and services can depend on the concrete guard without
     * touching the auth() facade.
     */
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

    /**
     * Wire the currency strategy: any consumer that depends on the
     * provider contract gets the TCMB implementation, fed by config.
     * The ExchangeRateService gets a Redis-backed cache repository and
     * the configured TTL.
     */
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

        // CurrencyConverter is stateless (depends only on the ExchangeRateService
        // singleton above), so a single instance serves every resource within a
        // request rather than being rebuilt through the container on each
        // iteration of a paginated list.
        $this->app->singleton(CurrencyConverter::class);
    }

    /**
     * Two named rate limiters back the throttle middleware:
     *
     *  - "api"  — the default for any authenticated endpoint, per-user
     *             when authenticated and per-IP otherwise. 60/min.
     *  - "auth" — the unauthenticated login/register/refresh surface,
     *             keyed strictly by IP. 5/min keeps brute-force pressure
     *             on credential endpoints minimal without ruining UX.
     */
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
