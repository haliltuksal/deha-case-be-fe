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

it('sets a cart item to an absolute quantity', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 20]);
    $token = JWTAuth::fromUser($user);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 3]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/cart/items/{$product->id}", ['quantity' => 7])
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 7);
});

it('rejects an absolute quantity that exceeds stock', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $token = JWTAuth::fromUser($user);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/cart/items/{$product->id}", ['quantity' => 10])
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INSUFFICIENT_STOCK');
});

it('rejects updating a product that is not in the cart', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/cart/items/{$product->id}", ['quantity' => 1])
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});

it('rejects quantities below 1 with validation', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);
    $token = JWTAuth::fromUser($user);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/cart/items/{$product->id}", ['quantity' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity']);
});
