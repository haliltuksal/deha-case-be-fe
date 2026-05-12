<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns 204 and invalidates the bearer token', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    // The blacklisted token must no longer authorize requests.
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});

it('rejects logout without an authentication token', function (): void {
    $this->postJson('/api/v1/auth/logout')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});
