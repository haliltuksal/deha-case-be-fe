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

it('returns a paginated list of products with prices in every supported currency', function (): void {
    Product::factory()->count(3)->create(['base_currency' => 'TRY']);

    $response = $this->getJson('/api/v1/products?per_page=2');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'items' => [
                    ['id', 'name', 'stock', 'base_currency', 'price', 'prices' => ['TRY', 'USD', 'EUR']],
                ],
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ])
        ->assertJsonPath('status', 'success')
        ->assertJsonCount(2, 'data.items')
        ->assertJsonPath('data.pagination.total', 3);
});

it('filters products whose name or description matches the search term', function (): void {
    Product::factory()->create(['name' => 'Türk Kahvesi', 'description' => 'Geleneksel']);
    Product::factory()->create(['name' => 'Filtre Kahvesi', 'description' => 'Arabica']);
    Product::factory()->create(['name' => 'Çay', 'description' => 'Rize']);

    $response = $this->getJson('/api/v1/products?search=kahve');

    $response->assertOk()
        ->assertJsonCount(2, 'data.items');
});

it('caps per_page at 100 and rejects non-positive values', function (): void {
    $this->getJson('/api/v1/products?per_page=0')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);

    $this->getJson('/api/v1/products?per_page=999')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});

it('converts prices according to the cached exchange rates', function (): void {
    Product::factory()->create([
        'name' => 'Kahve',
        'price' => '100.00',
        'base_currency' => 'TRY',
    ]);

    $response = $this->getJson('/api/v1/products?per_page=1');

    // 100 TRY → USD = 100 / 32.5407 ≈ 3.07
    // 100 TRY → EUR = 100 / 34.9203 ≈ 2.86
    $response->assertOk()
        ->assertJsonPath('data.items.0.prices.TRY', '100.00')
        ->assertJsonPath('data.items.0.prices.USD', '3.07')
        ->assertJsonPath('data.items.0.prices.EUR', '2.86');
});

it('exposes the base currency price as the canonical price field', function (): void {
    Product::factory()->create([
        'price' => '24.99',
        'base_currency' => 'USD',
    ]);

    $response = $this->getJson('/api/v1/products?per_page=1');

    $response->assertJsonPath('data.items.0.base_currency', 'USD')
        ->assertJsonPath('data.items.0.price', '24.99')
        ->assertJsonPath('data.items.0.prices.USD', '24.99');
});
