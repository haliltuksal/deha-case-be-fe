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

function adminToken(): string
{
    $admin = User::factory()->admin()->create();

    return JWTAuth::fromUser($admin);
}

function customerToken(): string
{
    $customer = User::factory()->create();

    return JWTAuth::fromUser($customer);
}

it('lets an admin create a product and persists it', function (): void {
    $token = adminToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [
            'name' => 'New Coffee',
            'description' => 'Tasty.',
            'price' => '49.99',
            'base_currency' => 'TRY',
            'stock' => 10,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Coffee')
        ->assertJsonPath('data.base_currency', 'TRY')
        ->assertJsonPath('data.price', '49.99');

    expect(Product::query()->where('name', 'New Coffee')->exists())->toBeTrue();
});

it('returns a Location header on creation pointing at the new resource', function (): void {
    $token = adminToken();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [
            'name' => 'Tea',
            'price' => '15.00',
            'base_currency' => 'TRY',
            'stock' => 50,
        ]);

    $response->assertCreated();
    /** @var int $id */
    $id = $response->json('data.id');
    expect($response->headers->get('Location'))->toContain("/api/v1/products/{$id}");
});

it('rejects unauthenticated create attempts', function (): void {
    $this->postJson('/api/v1/products', [
        'name' => 'X',
        'price' => '1.00',
        'base_currency' => 'TRY',
        'stock' => 1,
    ])
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});

it('rejects create attempts from non-admin users', function (): void {
    $token = customerToken();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [
            'name' => 'Sneaky',
            'price' => '1.00',
            'base_currency' => 'TRY',
            'stock' => 1,
        ])
        ->assertStatus(403)
        ->assertJsonPath('code', 'ERR_UNAUTHORIZED');
});

it('validates required fields on create', function (): void {
    $token = adminToken();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price', 'base_currency', 'stock']);
});

it('rejects negative price and negative stock', function (): void {
    $token = adminToken();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [
            'name' => 'Bad',
            'price' => '-1.00',
            'base_currency' => 'TRY',
            'stock' => -5,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price', 'stock']);
});

it('rejects an unknown base currency', function (): void {
    $token = adminToken();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [
            'name' => 'Bad',
            'price' => '10.00',
            'base_currency' => 'XYZ',
            'stock' => 1,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['base_currency']);
});
