<?php

declare(strict_types=1);

it('returns a successful response from the health endpoint', function (): void {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
