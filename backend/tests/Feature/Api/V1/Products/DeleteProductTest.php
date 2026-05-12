<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('lets an admin delete a product and removes it from the database', function (): void {
    $product = Product::factory()->create();
    $admin = User::factory()->admin()->create();
    $token = JWTAuth::fromUser($admin);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/products/{$product->id}")
        ->assertNoContent();

    expect(Product::query()->find($product->id))->toBeNull();
});

it('rejects delete by a non-admin user', function (): void {
    $product = Product::factory()->create();
    $customer = User::factory()->create();
    $token = JWTAuth::fromUser($customer);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/products/{$product->id}")
        ->assertStatus(403)
        ->assertJsonPath('code', 'ERR_UNAUTHORIZED');

    expect(Product::query()->find($product->id))->not->toBeNull();
});

it('returns 404 when deleting a non-existent product', function (): void {
    $admin = User::factory()->admin()->create();
    $token = JWTAuth::fromUser($admin);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/products/9999')
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});
