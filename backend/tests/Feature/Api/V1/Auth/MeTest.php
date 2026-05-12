<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns the current user payload when given a valid token', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.is_admin', false);
});

it('rejects requests without a token', function (): void {
    $this->getJson('/api/v1/auth/me')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});

it('rejects requests with a malformed token', function (): void {
    // The auth:api middleware deliberately collapses every JWT failure
    // (invalid, expired, blacklisted) into the opaque ERR_UNAUTHENTICATED
    // code so clients cannot probe token state. Specific codes are emitted
    // only when JWT operations bubble up outside the middleware.
    $this->withHeader('Authorization', 'Bearer this-is-not-a-real-token')
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});
