<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

final class PaginatedOrderCollection extends BasePaginatedCollection
{
    /** @var class-string<OrderResource> */
    public $collects = OrderResource::class;
}
