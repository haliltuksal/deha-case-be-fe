<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    Cache::put('exchange_rate:USD', '32.5407', 3600);
    Cache::put('exchange_rate:EUR', '34.9203', 3600);
});

it('returns a single product with the canonical envelope', function (): void {
    $product = Product::factory()->create([
        'name' => 'Espresso Makinesi',
        'price' => '299.00',
        'base_currency' => 'USD',
        'stock' => 8,
    ]);

    $this->getJson("/api/v1/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', 'Espresso Makinesi')
        ->assertJsonPath('data.stock', 8)
        ->assertJsonPath('data.base_currency', 'USD')
        ->assertJsonPath('data.price', '299.00')
        ->assertJsonStructure(['data' => ['prices' => ['TRY', 'USD', 'EUR']]]);
});

it('returns 404 for a non-existent product', function (): void {
    $this->getJson('/api/v1/products/9999')
        ->assertStatus(404)
        ->assertJsonPath('code', 'ERR_NOT_FOUND');
});
