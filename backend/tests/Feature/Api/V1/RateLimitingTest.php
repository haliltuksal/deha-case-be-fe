<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    RateLimiter::clear('auth|127.0.0.1');
});

it('throttles the auth login surface to five attempts per minute per ip', function (): void {
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ghost@example.com',
            'password' => 'wrong-password',
        ]);
        // The fifth request still goes through and returns 401, not 429.
        expect($response->getStatusCode())->toBeIn([401, 422]);
    }

    $sixth = $this->postJson('/api/v1/auth/login', [
        'email' => 'ghost@example.com',
        'password' => 'wrong-password',
    ]);

    $sixth->assertStatus(429)
        ->assertJsonPath('code', 'ERR_TOO_MANY_REQUESTS');
});

it('throttles the registration surface together with login', function (): void {
    for ($i = 1; $i <= 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'x@example.com',
            'password' => 'irrelevant',
        ]);
    }

    // The 6th call to the SAME auth-throttle bucket should be blocked
    // even though it goes to a different endpoint within the bucket.
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Sample',
        'email' => 'sample@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertStatus(429);
});

it('uses a separate, more generous bucket for the api throttle', function (): void {
    // The product list is unauthenticated, sits on `throttle:api`, and only
    // touches the database — making it the right surface to verify the
    // generous (60/min) bucket actually lets a burst of 30 requests through.
    for ($i = 1; $i <= 30; $i++) {
        $this->getJson('/api/v1/products')->assertOk();
    }
});
