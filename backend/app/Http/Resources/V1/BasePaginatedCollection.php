<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Common parent for every paginated v1 API collection. Emits the canonical
 * envelope shape with pagination metadata folded into the data payload:
 *
 *   {
 *     "status":  "success",
 *     "message": null,
 *     "data": {
 *       "items": [ ...resource fields per item... ],
 *       "pagination": {
 *         "current_page": 1,
 *         "last_page": 5,
 *         "per_page": 12,
 *         "total": 50
 *       }
 *     }
 *   }
 *
 * Subclasses only need to declare `public $collects = SomeResource::class;`
 * to bind a per-item resource transformer.
 */
abstract class BasePaginatedCollection extends ResourceCollection
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
                'data' => [
                    'items' => $this->collection?->map(static fn ($item) => $item->resolve($request))->all() ?? [],
                    'pagination' => $this->buildPagination(),
                ],
            ],
            status: $statusCode,
        );
    }

    /**
     * @return array<string, int>
     */
    private function buildPagination(): array
    {
        $resource = $this->resource;
        if ($resource instanceof LengthAwarePaginator) {
            return [
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
            ];
        }

        $count = $this->collection?->count() ?? 0;

        return [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $count,
            'total' => $count,
        ];
    }
}
