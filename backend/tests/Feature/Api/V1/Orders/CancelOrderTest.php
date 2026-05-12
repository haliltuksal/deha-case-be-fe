<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
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

function createPendingOrderWithItem(User $user, Product $product, int $quantity): Order
{
    $order = Order::query()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => '100.00',
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit_price' => $product->price,
        'base_currency' => $product->base_currency->value,
        'quantity' => $quantity,
        'line_total' => bcmul($product->price, (string) $quantity, 2),
    ]);

    return $order;
}

it('transitions a pending order to cancelled and returns stock to every line', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['stock' => 5]);
    $order = createPendingOrderWithItem($user, $product, 3);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    $product->refresh();
    expect($product->stock)->toBe(8);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::CANCELLED);
});

it('rejects cancellation of an already-cancelled order', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['stock' => 5]);
    $order = createPendingOrderWithItem($user, $product, 1);
    $order->update(['status' => OrderStatus::CANCELLED->value]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INVALID_ORDER_TRANSITION')
        ->assertJsonPath('details.current_status', 'cancelled');
});

it('rejects cancellation of a completed order', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['stock' => 5]);
    $order = createPendingOrderWithItem($user, $product, 1);
    $order->update(['status' => OrderStatus::COMPLETED->value]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INVALID_ORDER_TRANSITION');
});

it('returns 404 when cancelling another users order', function (): void {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $token = JWTAuth::fromUser($alice);
    $product = Product::factory()->create(['stock' => 5]);
    $bobsOrder = createPendingOrderWithItem($bob, $product, 1);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$bobsOrder->id}/cancel")
        ->assertStatus(404);
});
