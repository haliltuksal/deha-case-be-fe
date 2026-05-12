<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
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

it('returns the order detail when it belongs to the authenticated user', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $order = Order::query()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => '150.00',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $order->id)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_amount', '150.00')
        ->assertJsonStructure(['data' => ['totals' => ['TRY', 'USD', 'EUR']]]);
});

it('returns 404 for a non-existent order', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/orders/9999')
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});

it('returns 404 — not 403 — when the order belongs to another user', function (): void {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $token = JWTAuth::fromUser($alice);

    $bobsOrder = Order::query()->create([
        'user_id' => $bob->id,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => '200.00',
    ]);

    // 404 over 403 keeps existence undisclosed.
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$bobsOrder->id}")
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});
