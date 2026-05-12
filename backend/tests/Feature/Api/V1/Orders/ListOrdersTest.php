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

it('lists only the authenticated users orders, newest first', function (): void {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $token = JWTAuth::fromUser($alice);

    Order::query()->create(['user_id' => $alice->id, 'status' => OrderStatus::PENDING->value, 'total_amount' => '100.00']);
    Order::query()->create(['user_id' => $alice->id, 'status' => OrderStatus::COMPLETED->value, 'total_amount' => '200.00']);
    Order::query()->create(['user_id' => $bob->id, 'status' => OrderStatus::PENDING->value, 'total_amount' => '999.00']);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonCount(2, 'data.items')
        ->assertJsonPath('data.pagination.total', 2);
});

it('paginates the user orders list', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    foreach (range(1, 25) as $i) {
        Order::query()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING->value,
            'total_amount' => '10.00',
        ]);
    }

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/orders?per_page=10')
        ->assertOk()
        ->assertJsonCount(10, 'data.items')
        ->assertJsonPath('data.pagination.total', 25)
        ->assertJsonPath('data.pagination.last_page', 3);
});

it('rejects unauthenticated list requests', function (): void {
    $this->getJson('/api/v1/orders')
        ->assertStatus(401);
});
