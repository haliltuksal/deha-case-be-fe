<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\ListProductsAction;
use App\Actions\Product\ShowProductAction;
use App\Actions\Product\UpdateProductAction;
use App\DTOs\Product\ProductData;
use App\DTOs\Product\ProductFilterData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ProductService
{
    public function __construct(
        private CreateProductAction $createAction,
        private UpdateProductAction $updateAction,
        private DeleteProductAction $deleteAction,
        private ListProductsAction $listAction,
        private ShowProductAction $showAction,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function list(ProductFilterData $filter): LengthAwarePaginator
    {
        return $this->listAction->execute($filter);
    }

    public function show(int $id): Product
    {
        return $this->showAction->execute($id);
    }

    public function create(ProductData $data): Product
    {
        return $this->createAction->execute($data);
    }

    public function update(Product $product, ProductData $data): Product
    {
        return $this->updateAction->execute($product, $data);
    }

    public function delete(Product $product): void
    {
        $this->deleteAction->execute($product);
    }
}
