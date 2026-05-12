<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Product\ProductFilterData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListProductsAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function execute(ProductFilterData $filter): LengthAwarePaginator
    {
        return $this->products->paginate($filter);
    }
}
