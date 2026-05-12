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

it('lazy-creates an empty cart on the first GET', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.item_count', 0)
        ->assertJsonPath('data.total_quantity', 0)
        ->assertJsonPath('data.totals.TRY', '0.00')
        ->assertJsonPath('data.totals.USD', '0.00')
        ->assertJsonPath('data.totals.EUR', '0.00');

    expect(Cart::query()->where('user_id', $user->id)->exists())->toBeTrue();
});

it('renders cart items with multi-currency subtotals and aggregate totals', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $cart = Cart::query()->create(['user_id' => $user->id]);

    $product = Product::factory()->create([
        'price' => '100.00',
        'base_currency' => 'TRY',
        'stock' => 50,
    ]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 3]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.item_count', 1)
        ->assertJsonPath('data.total_quantity', 3)
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 3)
        ->assertJsonPath('data.items.0.unit_price', '100.00')
        ->assertJsonPath('data.items.0.subtotal.TRY', '300.00')
        ->assertJsonPath('data.totals.TRY', '300.00');
});

it('rejects unauthenticated cart access', function (): void {
    $this->getJson('/api/v1/cart')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});
