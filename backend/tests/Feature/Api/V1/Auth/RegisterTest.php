<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a new user and returns a bearer token plus the user payload', function (): void {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Halil Tuksal',
        'email' => 'halil@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email', 'is_admin', 'created_at'],
            ],
        ])
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonPath('data.user.email', 'halil@example.com')
        ->assertJsonPath('data.user.is_admin', false);

    expect(User::query()->where('email', 'halil@example.com')->exists())->toBeTrue();

    $persisted = User::query()->where('email', 'halil@example.com')->firstOrFail();
    expect(Hash::check('secret123', $persisted->password))->toBeTrue();
});

it('rejects registration when required fields are missing', function (): void {
    $this->postJson('/api/v1/auth/register', [])
        ->assertStatus(422)
        ->assertJsonPath('code', 'ERR_VALIDATION')
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects registration with a malformed email', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Halil',
        'email' => 'not-an-email',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects a password that fails the strength rules', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Halil',
        'email' => 'halil@example.com',
        'password' => 'shorty',
        'password_confirmation' => 'shorty',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('rejects a password that does not match its confirmation', function (): void {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Halil',
        'email' => 'halil@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'different1',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('rejects registration when the email is already taken', function (): void {
    User::factory()->create(['email' => 'halil@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Halil',
        'email' => 'halil@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email'])
        ->assertJsonPath('errors.email.0', 'Bu e-posta adresi zaten kayıtlı.');
});
