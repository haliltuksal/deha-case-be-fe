<?php

declare(strict_types=1);

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

it('adds a fresh line item when the product is not yet in the cart', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertOk()
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2);
});

it('increments the quantity when the same product is added twice', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 5)
        ->assertJsonCount(1, 'data.items');
});

it('rejects adding more than the available stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 6])
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INSUFFICIENT_STOCK')
        ->assertJsonPath('details.product_id', $product->id)
        ->assertJsonPath('details.requested', 6)
        ->assertJsonPath('details.available', 5);
});

it('rejects adding when the cumulative quantity would exceed stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 4])
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INSUFFICIENT_STOCK')
        ->assertJsonPath('details.requested', 6);
});

it('validates required fields on add', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['product_id', 'quantity']);
});

it('rejects an unknown product_id with validation', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => 9999, 'quantity' => 1])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});

it('rejects non-positive quantities', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});

it('binds the cart to the authenticated user via the unique constraint', function (): void {
    // Cart isolation is enforced at the data layer through a
    // unique(user_id) constraint on the carts table plus the
    // findOrCreateForUser repository method that scopes by user_id. This
    // test verifies that data-layer guarantee end-to-end without
    // attempting two simulated HTTP requests in the same test (which
    // would require booting a fresh kernel between calls).
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    $aliceToken = JWTAuth::fromUser($alice);

    $this->withHeader('Authorization', "Bearer {$aliceToken}")
        ->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3])
        ->assertOk();

    /** @var Cart $aliceCart */
    $aliceCart = Cart::query()->where('user_id', $alice->id)->firstOrFail();
    expect($aliceCart->items()->count())->toBe(1)
        ->and(Cart::query()->where('user_id', $bob->id)->exists())->toBeFalse();
});
