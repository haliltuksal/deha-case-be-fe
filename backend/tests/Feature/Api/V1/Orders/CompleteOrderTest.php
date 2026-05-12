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

it('lets an admin complete a pending order without touching stock', function (): void {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();
    $token = JWTAuth::fromUser($admin);
    $product = Product::factory()->create(['stock' => 5]);

    $order = Order::query()->create([
        'user_id' => $owner->id,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => '50.00',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit_price' => '50.00',
        'base_currency' => 'TRY',
        'quantity' => 1,
        'line_total' => '50.00',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/complete")
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    $product->refresh();
    expect($product->stock)->toBe(5);
});

it('rejects complete attempts from non-admin users', function (): void {
    $customer = User::factory()->create();
    $token = JWTAuth::fromUser($customer);

    $order = Order::query()->create([
        'user_id' => $customer->id,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => '50.00',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/complete")
        ->assertStatus(403)
        ->assertJsonPath('code', 'ERR_UNAUTHORIZED');
});

it('rejects completing a cancelled order', function (): void {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();
    $token = JWTAuth::fromUser($admin);

    $order = Order::query()->create([
        'user_id' => $owner->id,
        'status' => OrderStatus::CANCELLED->value,
        'total_amount' => '50.00',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/complete")
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_INVALID_ORDER_TRANSITION');
});
