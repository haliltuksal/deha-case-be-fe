<?php

declare(strict_types=1);

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\LogApiRequest;
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
    if (app()->environment() !== 'production' && config('app.debug') === true) {
        $extras['debug'] = [
            'exception' => $e::class,
            'message' => $e->getMessage(),
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
            LogApiRequest::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->redirectGuestsTo(
            static fn (Request $request): ?string => $request->is('api/*') ? null : route('login'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions) use ($renderApiException): void {
        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request, Throwable $e): bool => $request->is('api/*'),
        );

        $exceptions->render($renderApiException);
    })->create();
