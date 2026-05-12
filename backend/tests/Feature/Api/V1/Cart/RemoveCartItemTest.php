<?php

declare(strict_types=1);

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('removes a cart item and returns 204', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $token = JWTAuth::fromUser($user);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/cart/items/{$product->id}")
        ->assertNoContent();

    expect(CartItem::query()->where('cart_id', $cart->id)->count())->toBe(0);
});

it('returns 404 when removing a product that is not in the cart', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/cart/items/9999')
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});
