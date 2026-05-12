<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Product\ProductData;
use App\Models\Product;

final readonly class CreateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function execute(ProductData $data): Product
    {
        return $this->products->create($data);
    }
}
