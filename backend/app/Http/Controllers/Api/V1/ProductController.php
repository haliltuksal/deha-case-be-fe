<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Product\ProductData;
use App\DTOs\Product\ProductFilterData;
use App\Http\Requests\Product\ListProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\V1\PaginatedProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class ProductController extends BaseApiController
{
    public function __construct(
        private readonly ProductService $service,
    ) {}

    /**
     * List products
     *
     * Paginated catalogue. Each product carries `prices` in TRY/USD/EUR
     * computed from the latest cached TCMB rates.
     *
     * @group Products
     *
     * @unauthenticated
     *
     * @queryParam search string Filter by name or description (LIKE %term%). Example: kahve
     * @queryParam per_page integer Page size, 1–100 (default 15). Example: 15
     * @queryParam page integer Page number, 1-based (default 1). Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "name": "Türk Kahvesi 250g",
     *         "description": "Geleneksel Türk kahvesi, taze çekilmiş.",
     *         "stock": 80,
     *         "base_currency": "TRY",
     *         "price": "95.00",
     *         "prices": {"TRY": "95.00", "USD": "2.10", "EUR": "1.79"},
     *         "created_at": "2026-05-04T18:00:00+00:00",
     *         "updated_at": "2026-05-04T18:00:00+00:00"
     *       }
     *     ],
     *     "pagination": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 10}
     *   }
     * }
     */
    public function index(ListProductsRequest $request): JsonResponse
    {
        $paginator = $this->service->list(ProductFilterData::fromRequest($request));

        return $this->respondOk(new PaginatedProductCollection($paginator));
    }

    /**
     * Show product
     *
     * Fetch a single product with its multi-currency price block.
     *
     * @group Products
     *
     * @unauthenticated
     *
     * @urlParam id integer required Product id. Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "name": "Türk Kahvesi 250g",
     *     "description": "Geleneksel Türk kahvesi, taze çekilmiş.",
     *     "stock": 80,
     *     "base_currency": "TRY",
     *     "price": "95.00",
     *     "prices": {"TRY": "95.00", "USD": "2.10", "EUR": "1.79"},
     *     "created_at": "2026-05-04T18:00:00+00:00",
     *     "updated_at": "2026-05-04T18:00:00+00:00"
     *   }
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "The requested resource was not found.",
     *   "data": null,
     *   "code": "ERR_NOT_FOUND"
     * }
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->service->show($id);

        return $this->respondOk(ProductResource::make($product));
    }

    /**
     * Create product (admin)
     *
     * Add a new product to the catalogue. Requires an admin bearer token.
     *
     * @group Products
     *
     * @authenticated
     *
     * @bodyParam name string required Product name (2–255 chars). Example: Test Product
     * @bodyParam description string Optional description (≤ 5000 chars). Example: Premium quality.
     * @bodyParam price numeric required Decimal with up to two fraction digits, ≥ 0. Example: 99.99
     * @bodyParam base_currency string required One of TRY, USD, EUR. Example: TRY
     * @bodyParam stock integer required Non-negative quantity on hand. Example: 10
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Ürün oluşturuldu.",
     *   "data": {
     *     "id": 11,
     *     "name": "Test Product",
     *     "description": "Premium quality.",
     *     "stock": 10,
     *     "base_currency": "TRY",
     *     "price": "99.99",
     *     "prices": {"TRY": "99.99", "USD": "2.21", "EUR": "1.89"},
     *     "created_at": "2026-05-04T20:00:00+00:00",
     *     "updated_at": "2026-05-04T20:00:00+00:00"
     *   }
     * }
     * @response 403 scenario="not an admin" {
     *   "status": "error",
     *   "message": "Administrator privileges are required.",
     *   "data": null,
     *   "code": "ERR_UNAUTHORIZED"
     * }
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->create(ProductData::fromRequest($request));

        return $this->respondCreated(
            ProductResource::make($product),
            location: route('products.show', ['product' => $product->id]),
            message: 'Ürün oluşturuldu.',
        );
    }

    /**
     * Update product (admin)
     *
     * Patch any subset of fields on an existing product. Fields you omit
     * stay untouched.
     *
     * @group Products
     *
     * @authenticated
     *
     * @urlParam id integer required Product id. Example: 11
     *
     * @bodyParam name string Updated name. Example: Renamed Product
     * @bodyParam description string Updated description.
     * @bodyParam price numeric Updated price (decimal:0,2 ≥ 0). Example: 149.99
     * @bodyParam base_currency string TRY|USD|EUR. Example: USD
     * @bodyParam stock integer Updated stock. Example: 25
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Ürün güncellendi.",
     *   "data": {
     *     "id": 11,
     *     "name": "Renamed Product",
     *     "description": "Premium quality.",
     *     "stock": 25,
     *     "base_currency": "USD",
     *     "price": "149.99",
     *     "prices": {"TRY": "6776.39", "USD": "149.99", "EUR": "128.04"},
     *     "created_at": "2026-05-04T20:00:00+00:00",
     *     "updated_at": "2026-05-04T20:05:00+00:00"
     *   }
     * }
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->service->show($id);
        $updated = $this->service->update($product, ProductData::fromRequest($request));

        return $this->respondOk(ProductResource::make($updated), message: 'Ürün güncellendi.');
    }

    /**
     * Delete product (admin)
     *
     * Remove a product from the catalogue. Cart entries referencing it
     * are removed via cascade; existing order_item snapshots survive
     * unchanged (FK is SET NULL on the order_items side).
     *
     * @group Products
     *
     * @authenticated
     *
     * @urlParam id integer required Product id. Example: 11
     *
     * @response 204 {}
     */
    public function destroy(int $id): Response
    {
        $product = $this->service->show($id);
        $this->service->delete($product);

        return $this->respondNoContent();
    }
}
