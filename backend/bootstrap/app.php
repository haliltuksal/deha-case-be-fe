<?php

declare(strict_types=1);

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Support\Http\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Build the canonical error envelope. `status: 'error'` and `data: null`
 * sit alongside the existing `code`, `message`, `errors`, `details` keys
 * so the shape mirrors the success envelope from BaseResource.
 *
 * @param array<string, mixed> $extras
 */
$apiError = static function (
    int $status,
    string $code,
    string $message,
    array $extras = [],
): JsonResponse {
    return response()->json([
        'status' => 'error',
        'message' => $message,
        'data' => null,
        'code' => $code,
    ] + $extras, $status);
};

/**
 * Single-source API exception renderer. Inspecting the path once at the top
 * keeps the framework's HTML / web error pages intact for non-API routes
 * (so artisan-rendered web pages still work) while every `/api/*` request
 * gets a canonical JSON envelope. Path matching is the explicit boundary;
 * we deliberately do not trust `$request->expectsJson()` (which depends on
 * the client sending `Accept: application/json`) for this decision.
 */
$renderApiException = static function (Throwable $e, Request $request) use ($apiError): ?JsonResponse {
    if (! $request->is('api/*')) {
        return null;
    }

    if ($e instanceof ApiException) {
        return $e->toResponse($request);
    }

    if ($e instanceof ValidationException) {
        return $apiError(
            HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
            'ERR_VALIDATION',
            $e->getMessage(),
            ['errors' => $e->errors()],
        );
    }

    if ($e instanceof AuthenticationException) {
        return $apiError(
            HttpResponse::HTTP_UNAUTHORIZED,
            'ERR_UNAUTHENTICATED',
            $e->getMessage() !== '' ? $e->getMessage() : 'Unauthenticated.',
        );
    }

    if ($e instanceof TokenExpiredException) {
        return $apiError(HttpResponse::HTTP_UNAUTHORIZED, 'ERR_TOKEN_EXPIRED', 'The access token has expired.');
    }

    if ($e instanceof TokenBlacklistedException) {
        return $apiError(HttpResponse::HTTP_UNAUTHORIZED, 'ERR_TOKEN_BLACKLISTED', 'The access token has been revoked.');
    }

    if ($e instanceof TokenInvalidException) {
        return $apiError(HttpResponse::HTTP_UNAUTHORIZED, 'ERR_TOKEN_INVALID', 'The access token is invalid.');
    }

    if ($e instanceof JWTException) {
        return $apiError(HttpResponse::HTTP_UNAUTHORIZED, 'ERR_TOKEN_ABSENT', 'A bearer token is required to access this resource.');
    }

    if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
        // Laravel's prepareException() converts an untyped AuthorizationException
        // into an AccessDeniedHttpException whose message is the literal
        // "Forbidden". Walk the previous-exception chain to recover the
        // original message so the client sees what the policy actually said.
        $message = $e->getMessage();
        $previous = $e->getPrevious();
        if ($e instanceof AccessDeniedHttpException
            && $message === 'Forbidden'
            && $previous instanceof AuthorizationException
            && $previous->getMessage() !== '') {
            $message = $previous->getMessage();
        }

        return $apiError(
            HttpResponse::HTTP_FORBIDDEN,
            'ERR_UNAUTHORIZED',
            $message !== '' ? $message : 'This action is unauthorized.',
        );
    }

    if ($e instanceof ModelNotFoundException) {
        return $apiError(HttpResponse::HTTP_NOT_FOUND, 'ERR_NOT_FOUND', 'The requested resource was not found.');
    }

    if ($e instanceof NotFoundHttpException) {
        // Laravel's prepareException() folds ModelNotFoundException into
        // NotFoundHttpException — distinguish the two cases through the
        // previous-exception chain so the response message is accurate.
        $previous = $e->getPrevious();
        $message = $previous instanceof ModelNotFoundException
            ? 'The requested resource was not found.'
            : 'The requested endpoint was not found.';

        return $apiError(HttpResponse::HTTP_NOT_FOUND, 'ERR_NOT_FOUND', $message);
    }

    if ($e instanceof MethodNotAllowedHttpException) {
        return $apiError(
            HttpResponse::HTTP_METHOD_NOT_ALLOWED,
            'ERR_METHOD_NOT_ALLOWED',
            'The HTTP method used is not allowed for this endpoint.',
        );
    }

    if ($e instanceof TooManyRequestsHttpException) {
        return $apiError(
            HttpResponse::HTTP_TOO_MANY_REQUESTS,
            'ERR_TOO_MANY_REQUESTS',
            'Too many requests. Please slow down.',
        );
    }

    if ($e instanceof HttpExceptionInterface) {
        return $apiError(
            $e->getStatusCode(),
            'ERR_HTTP',
            $e->getMessage() !== '' ? $e->getMessage() : 'HTTP error.',
        );
    }

    $extras = [];
    if (config('app.debug') === true) {
        $extras['debug'] = [
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }

    return $apiError(
        HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        'ERR_INTERNAL',
        'Internal server error.',
        $extras,
    );
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/v1.php',
        apiPrefix: 'api/v1',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            AssignRequestId::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Disable the redirect-to-login fallback that the Authenticate
        // middleware otherwise performs whenever a guest hits a guarded
        // route without `Accept: application/json`. Returning null forces
        // an AuthenticationException (rendered as ERR_UNAUTHENTICATED) for
        // every `/api/*` request regardless of headers, which is the same
        // header-independent boundary the JSON renderer relies on.
        $middleware->redirectGuestsTo(
            static fn (Request $request): ?string => $request->is('api/*') ? null : route('login'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions) use ($renderApiException): void {
        // Force JSON error rendering for every API path. The path is the
        // explicit boundary; we deliberately do not fall back to
        // `$request->expectsJson()` because that depends on a client-supplied
        // Accept header and would silently revert to HTML on stripped requests.
        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request, Throwable $e): bool => $request->is('api/*'),
        );

        $exceptions->render($renderApiException);
    })->create();
