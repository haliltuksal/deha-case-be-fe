<?php

declare(strict_types=1);

it('returns a 404 with the canonical error code for unknown api routes', function (): void {
    $response = $this->getJson('/api/v1/this-route-does-not-exist');

    $response->assertNotFound()
        ->assertJsonPath('code', 'ERR_NOT_FOUND')
        ->assertJsonStructure(['message', 'code']);
});

it('returns a 405 with the canonical error code for wrong http method', function (): void {
    $response = $this->postJson('/api/v1/health');

    $response->assertStatus(405)
        ->assertJsonPath('code', 'ERR_METHOD_NOT_ALLOWED');
});
