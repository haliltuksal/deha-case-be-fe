<?php

declare(strict_types=1);

namespace App\Support\Http;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

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

    abstract public function getStatusCode(): int;

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

    protected function defaultMessage(): string
    {
        return HttpResponse::$statusTexts[$this->getStatusCode()] ?? 'Error';
    }
}
