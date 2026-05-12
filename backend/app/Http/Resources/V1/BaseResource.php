<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Common parent for every v1 API resource. Replaces Laravel's default
 * `{ data: ... }` shape with the canonical envelope used across the API:
 *
 *   {
 *     "status":  "success",
 *     "message": null | "...",
 *     "data":    { ...resource fields... }
 *   }
 *
 * Subclasses only implement `toArray()`; the envelope and any optional
 * action message live here so every endpoint speaks the same shape.
 *
 * Pass an action message via `Resource::make($model)->additional(['message' => '...'])`.
 *
 * Status code defaults to 200; pass a different one via
 * `Resource::make($model)->additional(['status_code' => 201])`.
 */
abstract class BaseResource extends JsonResource
{
    public function toResponse($request): JsonResponse
    {
        $statusCode = is_int($this->additional['status_code'] ?? null)
            ? (int) $this->additional['status_code']
            : 200;

        $message = isset($this->additional['message']) && is_string($this->additional['message'])
            ? $this->additional['message']
            : null;

        return new JsonResponse(
            data: [
                'status' => 'success',
                'message' => $message,
                'data' => $this->resolve($request),
            ],
            status: $statusCode,
        );
    }
}
