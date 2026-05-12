<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assigns a correlation id to every API request and propagates it back
 * on the response. If the caller already supplied a sane X-Request-Id
 * we honour it (so a Next.js BFF can trace the same id end-to-end);
 * otherwise we generate a fresh UUIDv4. The id is added to the log
 * context so every entry written during this request is searchable by
 * the same identifier.
 */
final class AssignRequestId
{
    private const HEADER = 'X-Request-Id';

    private const MAX_LENGTH = 64;

    public function handle(Request $request, Closure $next): Response
    {
        $incoming = $request->headers->get(self::HEADER);
        $requestId = $this->normalize($incoming);

        $request->headers->set(self::HEADER, $requestId);
        Log::withContext(['request_id' => $requestId]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set(self::HEADER, $requestId);

        return $response;
    }

    /**
     * Accept the supplied id only if it is a printable, reasonably sized
     * value; otherwise generate a fresh one. This keeps log lines clean
     * and prevents header injection.
     */
    private function normalize(?string $candidate): string
    {
        if ($candidate === null || $candidate === '') {
            return (string) Str::uuid();
        }

        $candidate = trim($candidate);

        if (strlen($candidate) === 0 || strlen($candidate) > self::MAX_LENGTH) {
            return (string) Str::uuid();
        }

        if (preg_match('/^[A-Za-z0-9._\-]+$/', $candidate) !== 1) {
            return (string) Str::uuid();
        }

        return $candidate;
    }
}
