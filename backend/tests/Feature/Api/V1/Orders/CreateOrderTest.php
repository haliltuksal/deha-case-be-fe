<?php

declare(strict_types=1);

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
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

it('creates a pending order with snapshots, decrements stock, and clears the cart', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create([
        'name' => 'Coffee 1kg',
        'price' => '100.00',
        'base_currency' => 'TRY',
        'stock' => 10,
    ]);

    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 3]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders');

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.currency', 'TRY')
        ->assertJsonPath('data.total_amount', '300.00')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.product_name', 'Coffee 1kg')
        ->assertJsonPath('data.items.0.quantity', 3)
        ->assertJsonPath('data.items.0.unit_price', '100.00')
        ->assertJsonPath('data.items.0.line_total', '300.00');

    $product->refresh();
    expect($product->stock)->toBe(7)
        ->and(CartItem::query()->where('cart_id', $cart->id)->count())->toBe(0)
        ->and(Order::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('aggregates lines with mixed base currencies into the canonical TRY total', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $tryProduct = Product::factory()->create(['price' => '100.00', 'base_currency' => 'TRY', 'stock' => 10]);
    $usdProduct = Product::factory()->create(['price' => '10.00', 'base_currency' => 'USD', 'stock' => 10]);

    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $tryProduct->id, 'quantity' => 1]);
    $cart->items()->create(['product_id' => $usdProduct->id, 'quantity' => 1]);

    // 100 TRY + (10 USD × 32.5407) = 100 + 325.41 = 425.41 TRY
    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders');

    $response->assertCreated()
        ->assertJsonPath('data.total_amount', '425.41');
});

it('rejects an order created from an empty cart', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders')
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_EMPTY_CART');
});

it('rolls back stock and order creation when one line exceeds available stock', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $available = Product::factory()->create(['stock' => 10]);
    $scarce = Product::factory()->create(['stock' => 1]);

    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $available->id, 'quantity' => 2]);
    $cart->items()->create(['product_id' => $scarce->id, 'quantity' => 5]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders')
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INSUFFICIENT_STOCK');

    $available->refresh();
    $scarce->refresh();
    expect($available->stock)->toBe(10)
        ->and($scarce->stock)->toBe(1)
        ->and(Order::query()->count())->toBe(0)
        ->and(CartItem::query()->where('cart_id', $cart->id)->count())->toBe(2);
});

it('preserves order item snapshots even after the underlying product is mutated', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['name' => 'Original', 'price' => '100.00', 'stock' => 10]);

    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders')
        ->assertCreated();

    // Mutate the product post-order
    $product->update(['name' => 'Renamed', 'price' => '999.00']);

    $order = Order::query()->where('user_id', $user->id)->firstOrFail();
    expect($order->items()->first()->product_name)->toBe('Original')
        ->and($order->items()->first()->unit_price)->toBe('100.00')
        ->and($order->items()->first()->line_total)->toBe('200.00');
});

it('rejects unauthenticated order creation', function (): void {
    $this->postJson('/api/v1/orders')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});
