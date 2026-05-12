<?php

declare(strict_types=1);

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('removes all items from the cart', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $products = Product::factory()->count(3)->create();
    foreach ($products as $product) {
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
    }

    expect(CartItem::query()->count())->toBe(3);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/cart')
        ->assertNoContent();

    expect(CartItem::query()->count())->toBe(0)
        ->and(Cart::query()->where('user_id', $user->id)->exists())->toBeTrue();
});

it('is idempotent on an already-empty cart', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/cart')
        ->assertNoContent();
});
