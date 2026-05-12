<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('issues a new token and matches the canonical auth response shape', function (): void {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/refresh');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['access_token', 'token_type', 'expires_in', 'user' => ['id', 'email']],
        ])
        ->assertJsonPath('data.token_type', 'bearer');

    /** @var string $newToken */
    $newToken = $response->json('data.access_token');
    expect($newToken)->not->toBe($token);
});

it('rejects refresh without a token', function (): void {
    $this->postJson('/api/v1/auth/refresh')
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_UNAUTHENTICATED');
});
