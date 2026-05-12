<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

it('returns ok status when all dependencies are reachable', function (): void {
    Redis::shouldReceive('connection->command')
        ->with('PING')
        ->andReturn(true);

    $response = $this->getJson('/api/v1/health');

    $response->assertOk()
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonPath('data.services.database', 'ok')
        ->assertJsonPath('data.services.redis', 'ok')
        ->assertJsonStructure([
            'data' => ['status', 'timestamp', 'services' => ['database', 'redis']],
        ]);
});

it('returns 503 when redis cannot be reached', function (): void {
    Redis::shouldReceive('connection->command')
        ->with('PING')
        ->andThrow(new RuntimeException('redis is down'));

    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(503)
        ->assertJsonPath('data.status', 'degraded')
        ->assertJsonPath('data.services.redis', 'down')
        ->assertJsonPath('data.services.database', 'ok');
});

it('returns 503 when database cannot be reached', function (): void {
    DB::shouldReceive('connection->getPdo')
        ->andThrow(new RuntimeException('db is down'));

    Redis::shouldReceive('connection->command')
        ->with('PING')
        ->andReturn(true);

    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(503)
        ->assertJsonPath('data.services.database', 'down')
        ->assertJsonPath('data.services.redis', 'ok');
});
