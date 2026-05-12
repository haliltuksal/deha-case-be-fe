<?php

declare(strict_types=1);

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    Cache::put('exchange_rate:USD', '32.5407', 3600);
    Cache::put('exchange_rate:EUR', '34.9203', 3600);
});

function authedHeaders(User $user): array
{
    return ['Authorization' => 'Bearer ' . JWTAuth::fromUser($user)];
}

it('writes the cart snapshot to the cache after an add', function (): void {
    /** @var CartCacheRepositoryInterface $cache */
    $cache = app(CartCacheRepositoryInterface::class);
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    expect($cache->get($user->id))->toBeNull();

    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3])
        ->assertOk();

    $cached = $cache->get($user->id);
    expect($cached)->not->toBeNull()
        ->and($cached->userId)->toBe($user->id)
        ->and($cached->items)->toHaveCount(1)
        ->and($cached->items[0]['product_id'])->toBe($product->id)
        ->and($cached->items[0]['quantity'])->toBe(3);
});

it('updates the cache on quantity change', function (): void {
    /** @var CartCacheRepositoryInterface $cache */
    $cache = app(CartCacheRepositoryInterface::class);
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])
        ->assertOk();

    $this->withHeaders(authedHeaders($user))
        ->putJson("/api/v1/cart/items/{$product->id}", ['quantity' => 5])
        ->assertOk();

    $cached = $cache->get($user->id);
    expect($cached)->not->toBeNull()
        ->and($cached->items[0]['quantity'])->toBe(5);
});

it('updates the cache on item removal', function (): void {
    /** @var CartCacheRepositoryInterface $cache */
    $cache = app(CartCacheRepositoryInterface::class);
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertOk();

    $this->withHeaders(authedHeaders($user))
        ->deleteJson("/api/v1/cart/items/{$product->id}")
        ->assertNoContent();

    $cached = $cache->get($user->id);
    expect($cached)->not->toBeNull()
        ->and($cached->items)->toBe([]);
});

it('forgets the cache key on clear', function (): void {
    /** @var CartCacheRepositoryInterface $cache */
    $cache = app(CartCacheRepositoryInterface::class);
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertOk();

    expect($cache->get($user->id))->not->toBeNull();

    $this->withHeaders(authedHeaders($user))
        ->deleteJson('/api/v1/cart')
        ->assertNoContent();

    expect($cache->get($user->id))->toBeNull();
});

it('serves a cart from cache when present and falls back to db on miss', function (): void {
    /** @var CartCacheRepositoryInterface $cache */
    $cache = app(CartCacheRepositoryInterface::class);
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    // Populate via add
    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 4])
        ->assertOk();

    // Drop the cache so the next read goes through the DB miss path
    $cache->forget($user->id);
    expect($cache->get($user->id))->toBeNull();

    $this->withHeaders(authedHeaders($user))
        ->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 4);

    // The miss-path should have repopulated the cache.
    expect($cache->get($user->id))->not->toBeNull();
});

it('invalidates the cache on checkout', function (): void {
    /** @var CartCacheRepositoryInterface $cache */
    $cache = app(CartCacheRepositoryInterface::class);
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => '100.00']);

    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertOk();

    expect($cache->get($user->id))->not->toBeNull();

    $this->withHeaders(authedHeaders($user))
        ->postJson('/api/v1/orders')
        ->assertCreated();

    expect($cache->get($user->id))->toBeNull()
        ->and(Cart::query()->where('user_id', $user->id)->firstOrFail()->items()->count())->toBe(0);
});
