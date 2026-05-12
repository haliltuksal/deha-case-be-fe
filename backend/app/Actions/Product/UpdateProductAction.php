<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\Product\ProductData;
use App\Models\Product;

final readonly class UpdateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function execute(Product $product, ProductData $data): Product
    {
        return $this->products->update($product, $data);
    }
}
