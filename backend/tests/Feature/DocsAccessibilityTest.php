<?php

declare(strict_types=1);

it('serves the HTML docs at /docs once they have been generated', function (): void {
    if (! file_exists(resource_path('views/scribe/index.blade.php'))) {
        test()->markTestSkipped('Run `php artisan scribe:generate` to render the docs view.');
    }

    $this->get('/docs')
        ->assertOk()
        ->assertSee('Dehasoft Case API', false);
});

it('exposes the postman collection at /docs.postman', function (): void {
    if (! file_exists(storage_path('app/private/scribe/collection.json'))) {
        test()->markTestSkipped('Run `php artisan scribe:generate` to produce the postman collection.');
    }

    $response = $this->get('/docs.postman');

    $response->assertOk();

    /** @var array{info: array{name: string}, item: array<int, mixed>} $collection */
    $collection = $response->json();

    expect($collection['info']['name'])->toBe('Dehasoft Case API')
        ->and($collection['item'])->not->toBeEmpty();
});

it('exposes the openapi spec at /docs.openapi', function (): void {
    $path = storage_path('app/private/scribe/openapi.yaml');

    if (! file_exists($path)) {
        test()->markTestSkipped('Run `php artisan scribe:generate` to produce the openapi spec.');
    }

    // The route serves the file as a streamed BinaryFileResponse, so we
    // verify the route resolves and the underlying file's contents are
    // shaped correctly rather than reading the streamed response body.
    $this->get('/docs.openapi')->assertOk();

    $body = (string) file_get_contents($path);
    expect(str_contains($body, 'openapi:'))->toBeTrue()
        ->and(str_contains($body, 'Dehasoft Case API'))->toBeTrue();
});
