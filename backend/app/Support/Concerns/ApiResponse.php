<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Canonical envelope for every v1 API response:
 *
 *   {
 *     "status":  "success" | "error",
 *     "message": string|null,
 *     "data":    mixed
 *   }
 *
 * Resources extending `BaseResource` already produce this shape from their
 * `toResponse()`. This trait covers ad-hoc array payloads (e.g. health
 * probes) so controllers do not have to assemble the envelope by hand.
 */
trait ApiResponse
{
    /**
     * @param JsonResource|array<string, mixed>|null $payload
     */
    protected function respondOk(
        JsonResource|array|null $payload = null,
        int $status = HttpResponse::HTTP_OK,
        ?string $message = null,
    ): JsonResponse {
        if ($payload instanceof JsonResource) {
            if ($message !== null) {
                $payload = $payload->additional(['message' => $message]);
            }
            if ($status !== HttpResponse::HTTP_OK) {
                $payload = $payload->additional(['status_code' => $status]);
            }

            return $payload->response()->setStatusCode($status);
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $payload,
        ], $status);
    }

    /**
     * @param JsonResource|array<string, mixed>|null $payload
     */
    protected function respondCreated(
        JsonResource|array|null $payload = null,
        ?string $location = null,
        ?string $message = null,
    ): JsonResponse {
        $response = $this->respondOk($payload, HttpResponse::HTTP_CREATED, $message);

        if ($location !== null) {
            $response->header('Location', $location);
        }

        return $response;
    }

    protected function respondNoContent(): Response
    {
        return response()->noContent();
    }

    /**
     * @param array<string, mixed> $details
     * @param array<string, list<string>>|null $errors
     */
    protected function respondError(
        string $message,
        string $code,
        int $status = HttpResponse::HTTP_BAD_REQUEST,
        array $details = [],
        ?array $errors = null,
    ): JsonResponse {
        $body = [
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'code' => $code,
        ];

        if ($details !== []) {
            $body['details'] = $details;
        }

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}
