<?php

declare(strict_types=1);

namespace App\Support\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

/**
 * Base class for any domain exception that should be rendered as a uniform
 * JSON error to API clients. Subclasses declare their HTTP status code and
 * machine-readable error code; the rendering shape is enforced here.
 */
abstract class ApiException extends RuntimeException implements Responsable
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $message = '',
        private readonly array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * The HTTP status code that this exception maps to.
     */
    abstract public function getStatusCode(): int;

    /**
     * The machine-readable error code (e.g. "ERR_INSUFFICIENT_STOCK").
     */
    abstract public function getErrorCode(): string;

    /**
     * Optional structured payload describing the error context.
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param Request $request
     */
    public function toResponse($request): JsonResponse
    {
        $body = [
            'status' => 'error',
            'message' => $this->getMessage() !== '' ? $this->getMessage() : $this->defaultMessage(),
            'data' => null,
            'code' => $this->getErrorCode(),
        ];

        $details = $this->getDetails();
        if ($details !== []) {
            $body['details'] = $details;
        }

        return response()->json($body, $this->getStatusCode());
    }

    /**
     * Fallback message when the constructor was called without one.
     */
    protected function defaultMessage(): string
    {
        return HttpResponse::$statusTexts[$this->getStatusCode()] ?? 'Error';
    }
}
