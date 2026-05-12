<?php

declare(strict_types=1);

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

it('lets an admin update a product partially', function (): void {
    $product = Product::factory()->create(['price' => '100.00', 'stock' => 10]);
    $admin = User::factory()->admin()->create();
    $token = JWTAuth::fromUser($admin);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/products/{$product->id}", ['price' => '120.50', 'stock' => 5])
        ->assertOk()
        ->assertJsonPath('data.price', '120.50')
        ->assertJsonPath('data.stock', 5);

    $product->refresh();
    expect($product->price)->toBe('120.50')
        ->and($product->stock)->toBe(5);
});

it('rejects update by a non-admin user', function (): void {
    $product = Product::factory()->create();
    $customer = User::factory()->create();
    $token = JWTAuth::fromUser($customer);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/products/{$product->id}", ['stock' => 999])
        ->assertStatus(403)
        ->assertJsonPath('code', 'ERR_UNAUTHORIZED');
});

it('returns 404 when updating a non-existent product', function (): void {
    $admin = User::factory()->admin()->create();
    $token = JWTAuth::fromUser($admin);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/products/9999', ['stock' => 1])
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});

it('still validates field constraints on update', function (): void {
    $product = Product::factory()->create();
    $admin = User::factory()->admin()->create();
    $token = JWTAuth::fromUser($admin);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson("/api/v1/products/{$product->id}", ['stock' => -1, 'price' => '-99'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['stock', 'price']);
});
