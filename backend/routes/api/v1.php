<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| All routes registered here are mounted under the `/api/v1` prefix and
| served as JSON. Two named rate limiters back the throttle middleware:
| `auth` (5/min, IP-keyed) for unauthenticated credential endpoints, and
| `api` (60/min, user- or IP-keyed) for everything else. Health is left
| unthrottled so liveness probes never bounce off rate limits.
|
| --------------------------------------------------------------------------
| JWT decoding & validation (where it lives)
| --------------------------------------------------------------------------
| There is no bespoke JWT middleware in this codebase. Every route protected
| by `auth:api` runs through Laravel's stock `Authenticate` middleware, which
| resolves the `api` guard configured with the `jwt` driver in
| `config/auth.php`. The guard (php-open-source-saver/jwt-auth) reads the
| `Authorization: Bearer …` header, verifies the signature against
| `JWT_SECRET` with the algorithm in `JWT_ALGO`, and raises one of:
|
|   - TokenExpiredException     (token TTL elapsed)       → ERR_TOKEN_EXPIRED
|   - TokenBlacklistedException (logout / refresh used)   → ERR_TOKEN_BLACKLISTED
|   - TokenInvalidException     (signature mismatch)      → ERR_TOKEN_INVALID
|   - JWTException              (header missing/malformed)→ ERR_TOKEN_ABSENT
|
| `bootstrap/app.php` translates each of those into the canonical error
| envelope above. On success the resolved `User` is bound to the request
| and accessible via `$request->user()` or `auth()->user()`.
|
*/

Route::get('health', HealthController::class)->name('health');

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });

    Route::middleware(['auth:api', 'throttle:api'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::get('me', [AuthController::class, 'me'])->name('me');
    });
});

Route::middleware('throttle:api')->prefix('products')->name('products.')->group(function (): void {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('{product}', [ProductController::class, 'show'])
        ->whereNumber('product')
        ->name('show');

    Route::middleware(['auth:api', 'admin'])->group(function (): void {
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('{product}', [ProductController::class, 'update'])
            ->whereNumber('product')
            ->name('update');
        Route::delete('{product}', [ProductController::class, 'destroy'])
            ->whereNumber('product')
            ->name('destroy');
    });
});

Route::middleware(['auth:api', 'throttle:api'])->prefix('cart')->name('cart.')->group(function (): void {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');

    Route::prefix('items')->name('items.')->group(function (): void {
        Route::post('/', [CartController::class, 'addItem'])->name('store');
        Route::put('{productId}', [CartController::class, 'updateItem'])
            ->whereNumber('productId')
            ->name('update');
        Route::delete('{productId}', [CartController::class, 'removeItem'])
            ->whereNumber('productId')
            ->name('destroy');
    });
});

Route::middleware(['auth:api', 'throttle:api'])->prefix('orders')->name('orders.')->group(function (): void {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::get('{order}', [OrderController::class, 'show'])
        ->whereNumber('order')
        ->name('show');
    Route::post('{order}/cancel', [OrderController::class, 'cancel'])
        ->whereNumber('order')
        ->name('cancel');

    Route::middleware('admin')->group(function (): void {
        Route::post('{order}/complete', [OrderController::class, 'complete'])
            ->whereNumber('order')
            ->name('complete');
    });
});
