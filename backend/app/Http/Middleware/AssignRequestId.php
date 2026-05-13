<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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
