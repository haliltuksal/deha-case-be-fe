<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Product\ProductData;
use App\DTOs\Product\ProductFilterData;
use App\Models\Product;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function paginate(ProductFilterData $filter): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Product> $paginator */
        $paginator = $this->query()
            ->when($filter->search !== null && $filter->search !== '', function ($query) use ($filter): void {
                $term = '%' . $filter->search . '%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(perPage: $filter->perPage, page: $filter->page);

        return $paginator;
    }

    public function find(int $id): ?Product
    {
        /** @var Product|null $product */
        $product = parent::find($id);

        return $product;
    }

    public function findOrFail(int $id): Product
    {
        /** @var Product $product */
        $product = parent::findOrFail($id);

        return $product;
    }

    public function create(ProductData $data): Product
    {
        /** @var Product $product */
        $product = $this->query()->create($data->attributesForPersistence());

        return $product;
    }

    public function update(Product $product, ProductData $data): Product
    {
        $product->fill($data->attributesForPersistence())->save();

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
