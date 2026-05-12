<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;

final readonly class DeleteProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function execute(Product $product): void
    {
        $this->products->delete($product);
    }
}
