<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use App\Http\Requests\Product\ListProductsRequest;

final readonly class ProductFilterData
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    public static function fromRequest(ListProductsRequest $request): self
    {
        /** @var array{search?: string, per_page?: int|string, page?: int|string} $validated */
        $validated = $request->validated();

        return new self(
            search: $validated['search'] ?? null,
            perPage: isset($validated['per_page']) ? (int) $validated['per_page'] : 15,
            page: isset($validated['page']) ? (int) $validated['page'] : 1,
        );
    }
}
