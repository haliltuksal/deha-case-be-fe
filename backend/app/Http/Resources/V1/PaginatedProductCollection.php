<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

final class PaginatedProductCollection extends BasePaginatedCollection
{
    /** @var class-string<ProductResource> */
    public $collects = ProductResource::class;
}
