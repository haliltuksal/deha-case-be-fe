<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::middleware(['auth:api', EnsureUserIsAdmin::class])
        ->get('/api/_test/admin-only', fn () => response()->json(['ok' => true]));
});

it('lets an authenticated admin pass through', function (): void {
    $admin = User::factory()->admin()->create();
    $token = JWTAuth::fromUser($admin);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/_test/admin-only')
        ->assertOk()
        ->assertJsonPath('ok', true);
});

it('blocks an authenticated non-admin user with 403 ERR_UNAUTHORIZED', function (): void {
    $customer = User::factory()->create();
    $token = JWTAuth::fromUser($customer);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/_test/admin-only')
        ->assertStatus(403)
        ->assertJsonPath('code', 'ERR_UNAUTHORIZED');
});

it('blocks unauthenticated requests with 401 ERR_UNAUTHENTICATED before reaching the admin check', function (): void {
    $this->getJson('/api/_test/admin-only')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});
