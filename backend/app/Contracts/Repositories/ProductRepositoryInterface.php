<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTOs\Product\ProductData;
use App\DTOs\Product\ProductFilterData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(ProductFilterData $filter): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function findOrFail(int $id): Product;

    public function create(ProductData $data): Product;

    public function update(Product $product, ProductData $data): Product;

    public function delete(Product $product): void;
}
