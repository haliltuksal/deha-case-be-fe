<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate guarded routes to administrators only. Pair with `auth:api` so
 * authentication runs first and unauthenticated requests land on
 * AuthenticationException (401) before this middleware ever sees them.
 */
final class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->is_admin) {
            // Throw without ->withStatus(): Laravel's prepareException()
            // converts a status-less AuthorizationException into a Symfony
            // AccessDeniedHttpException that preserves the original message.
            // The global exception handler matches that converted form and
            // renders it with the canonical ERR_UNAUTHORIZED shape.
            throw new AuthorizationException('Administrator privileges are required.');
        }

        return $next($request);
    }
}
