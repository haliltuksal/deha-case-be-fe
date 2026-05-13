<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @template TResource of object
 *
 * @mixin TResource
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
