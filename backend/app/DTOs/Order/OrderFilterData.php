<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\Http\Requests\Order\ListOrdersRequest;

final readonly class OrderFilterData
{
    public function __construct(
        public int $perPage = 15,
        public int $page = 1,
    ) {}

    public static function fromRequest(ListOrdersRequest $request): self
    {
        /** @var array{per_page?: int|string, page?: int|string} $validated */
        $validated = $request->validated();

        return new self(
            perPage: isset($validated['per_page']) ? (int) $validated['per_page'] : 15,
            page: isset($validated['page']) ? (int) $validated['page'] : 1,
        );
    }
}
