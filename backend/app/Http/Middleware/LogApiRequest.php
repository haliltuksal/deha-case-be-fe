<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class LogApiRequest
{
    private const REDACT_KEYS = ['password', 'password_confirmation', 'token', 'access_token', 'refresh_token', 'authorization'];

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $status = $response->getStatusCode();

        $context = [
            'method' => $request->getMethod(),
            'path' => '/' . ltrim($request->path(), '/'),
            'status' => $status,
            'duration_ms' => $durationMs,
            'ip' => $request->ip(),
            'user_id' => $this->resolveUserId($request),
            'query' => $this->redact($request->query->all()),
        ];

        $level = match (true) {
            $status >= 500 => 'error',
            $status >= 400 => 'warning',
            default => 'info',
        };

        Log::channel('stack')->{$level}('api.request', $context);

        return $response;
    }

    private function resolveUserId(Request $request): int|string|null
    {
        $user = $request->user();
        if ($user === null) {
            return null;
        }

        $id = $user->getAuthIdentifier();
        if (is_int($id) || is_string($id)) {
            return $id;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::REDACT_KEYS, true)) {
                $data[$key] = '[REDACTED]';

                continue;
            }
            if (is_array($value)) {
                $data[$key] = $this->redact($value);
            }
        }

        return $data;
    }
}
