<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

final class HealthController extends BaseApiController
{
    /**
     * Health check
     *
     * Liveness / readiness probe. Returns the per-dependency state of the
     * database and redis. The HTTP status is 200 when every dependency
     * answers, otherwise 503 with the failing services flagged as `down`.
     *
     * @group Meta
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "status": "ok",
     *     "timestamp": "2026-05-04T20:00:00+00:00",
     *     "services": {
     *       "database": "ok",
     *       "redis": "ok"
     *     }
     *   }
     * }
     * @response 503 scenario="redis is unreachable" {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "status": "degraded",
     *     "timestamp": "2026-05-04T20:00:00+00:00",
     *     "services": {
     *       "database": "ok",
     *       "redis": "down"
     *     }
     *   }
     * }
     */
    public function __invoke(): JsonResponse
    {
        $services = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $overall = $this->isHealthy($services) ? 'ok' : 'degraded';
        $status = $overall === 'ok'
            ? HttpResponse::HTTP_OK
            : HttpResponse::HTTP_SERVICE_UNAVAILABLE;

        return $this->respondOk([
            'status' => $overall,
            'timestamp' => Carbon::now()->toIso8601String(),
            'services' => $services,
        ], $status);
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok';
        } catch (Throwable) {
            return 'down';
        }
    }

    private function checkRedis(): string
    {
        try {
            $response = Redis::connection()->command('PING');

            return $response === true || $response === 'PONG' ? 'ok' : 'down';
        } catch (Throwable) {
            return 'down';
        }
    }

    /**
     * @param array<string, string> $services
     */
    private function isHealthy(array $services): bool
    {
        foreach ($services as $state) {
            if ($state !== 'ok') {
                return false;
            }
        }

        return true;
    }
}
