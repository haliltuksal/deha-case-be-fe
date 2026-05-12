<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a freshly generated X-Request-Id header when the caller did not supply one', function (): void {
    $response = $this->getJson('/api/v1/products');

    $response->assertOk();
    $requestId = $response->headers->get('X-Request-Id');

    expect($requestId)->toBeString()
        ->and($requestId)->not->toBe('')
        ->and(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', (string) $requestId))->toBe(1);
});

it('preserves a sane caller-supplied X-Request-Id', function (): void {
    $supplied = 'bff-trace-abc123';

    $response = $this->withHeader('X-Request-Id', $supplied)
        ->getJson('/api/v1/products');

    expect($response->headers->get('X-Request-Id'))->toBe($supplied);
});

it('replaces a malformed X-Request-Id with a fresh uuid', function (): void {
    $response = $this->withHeader('X-Request-Id', 'bad@@inject(stuff)<script>')
        ->getJson('/api/v1/products');

    $requestId = (string) $response->headers->get('X-Request-Id');
    expect($requestId)->not->toBe('bad@@inject(stuff)<script>')
        ->and(preg_match('/^[0-9a-f-]{36}$/', $requestId))->toBe(1);
});

it('replaces an over-long X-Request-Id with a fresh uuid', function (): void {
    $response = $this->withHeader('X-Request-Id', str_repeat('a', 200))
        ->getJson('/api/v1/products');

    $requestId = (string) $response->headers->get('X-Request-Id');
    expect(strlen($requestId))->toBeLessThanOrEqual(64);
});
