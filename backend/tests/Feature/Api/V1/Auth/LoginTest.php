<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('returns a bearer token when valid credentials are provided', function (): void {
    User::factory()->create([
        'email' => 'halil@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'halil@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email', 'is_admin'],
            ],
        ])
        ->assertJsonPath('data.user.email', 'halil@example.com');
});

it('rejects login with the wrong password', function (): void {
    User::factory()->create([
        'email' => 'halil@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'halil@example.com',
        'password' => 'wrong-password',
    ])
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_INVALID_CREDENTIALS');
});

it('rejects login for an unknown email with the same canonical error', function (): void {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'ghost@example.com',
        'password' => 'whatever123',
    ])
        ->assertStatus(401)
        ->assertJsonPath('code', 'ERR_INVALID_CREDENTIALS');
});

it('validates required login fields', function (): void {
    $this->postJson('/api/v1/auth/login', [])
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_VALIDATION')
        ->assertJsonValidationErrors(['email', 'password']);
});
