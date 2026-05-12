<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Cart\AddToCartData;
use App\DTOs\Cart\UpdateCartItemData;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\V1\CartResource;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class CartController extends BaseApiController
{
    public function __construct(
        private readonly CartService $service,
    ) {}

    /**
     * Show cart
     *
     * Return the authenticated user's cart with each line's per-currency
     * subtotal and aggregated totals. The cart is created lazily on the
     * first call so a brand-new account always sees an empty cart shape.
     *
     * @group Cart
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "id": 1,
     *     "items": [
     *       {
     *         "product_id": 1,
     *         "name": "Türk Kahvesi 250g",
     *         "quantity": 2,
     *         "stock_available": 80,
     *         "unit_price": "95.00",
     *         "unit_currency": "TRY",
     *         "subtotal": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"}
     *       }
     *     ],
     *     "totals": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"},
     *     "item_count": 1,
     *     "total_quantity": 2
     *   }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        $cart = $this->service->get($this->user($request));

        return $this->respondOk(CartResource::make($cart));
    }

    /**
     * Add or increment cart item
     *
     * Push a product onto the cart. If the product is already in the
     * cart, the quantity is summed with the existing line. The combined
     * quantity must not exceed the product's stock.
     *
     * @group Cart
     *
     * @authenticated
     *
     * @bodyParam product_id integer required Existing product id. Example: 1
     * @bodyParam quantity integer required Positive delta to add (≥ 1). Example: 2
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Ürün sepete eklendi.",
     *   "data": {
     *     "id": 1,
     *     "items": [{"product_id": 1, "name": "Türk Kahvesi 250g", "quantity": 2, "stock_available": 80, "unit_price": "95.00", "unit_currency": "TRY", "subtotal": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"}}],
     *     "totals": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"},
     *     "item_count": 1,
     *     "total_quantity": 2
     *   }
     * }
     * @response 422 scenario="cumulative quantity exceeds stock" {
     *   "status": "error",
     *   "message": "Requested quantity exceeds the available stock.",
     *   "data": null,
     *   "code": "ERR_INSUFFICIENT_STOCK",
     *   "details": {"product_id": 1, "requested": 100, "available": 80}
     * }
     */
    public function addItem(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->service->add(
            $this->user($request),
            AddToCartData::fromRequest($request),
        );

        return $this->respondOk(CartResource::make($cart), message: 'Ürün sepete eklendi.');
    }

    /**
     * Set cart item quantity
     *
     * Set an existing cart line to an absolute quantity. Use DELETE to
     * remove the item entirely; setting quantity to 0 is rejected.
     *
     * @group Cart
     *
     * @authenticated
     *
     * @urlParam productId integer required Product id of the existing line. Example: 1
     *
     * @bodyParam quantity integer required New absolute quantity (≥ 1). Example: 5
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Sepet güncellendi.",
     *   "data": {
     *     "id": 1,
     *     "items": [{"product_id": 1, "name": "Türk Kahvesi 250g", "quantity": 5, "stock_available": 80, "unit_price": "95.00", "unit_currency": "TRY", "subtotal": {"TRY": "475.00", "USD": "10.51", "EUR": "8.97"}}],
     *     "totals": {"TRY": "475.00", "USD": "10.51", "EUR": "8.97"},
     *     "item_count": 1,
     *     "total_quantity": 5
     *   }
     * }
     * @response 404 scenario="product not in cart" {
     *   "status": "error",
     *   "message": "The requested resource was not found.",
     *   "data": null,
     *   "code": "ERR_NOT_FOUND"
     * }
     */
    public function updateItem(UpdateCartItemRequest $request, int $productId): JsonResponse
    {
        $cart = $this->service->update(
            $this->user($request),
            UpdateCartItemData::fromRequest($request, $productId),
        );

        return $this->respondOk(CartResource::make($cart), message: 'Sepet güncellendi.');
    }

    /**
     * Remove cart item
     *
     * Drop a single line from the cart.
     *
     * @group Cart
     *
     * @authenticated
     *
     * @urlParam productId integer required Product id of the line to remove. Example: 1
     *
     * @response 204 {}
     * @response 404 scenario="product not in cart" {
     *   "status": "error",
     *   "message": "The requested resource was not found.",
     *   "data": null,
     *   "code": "ERR_NOT_FOUND"
     * }
     */
    public function removeItem(Request $request, int $productId): Response
    {
        $this->service->remove($this->user($request), $productId);

        return $this->respondNoContent();
    }

    /**
     * Clear cart
     *
     * Drop every line from the cart. Idempotent — running it on an
     * already-empty cart still returns 204.
     *
     * @group Cart
     *
     * @authenticated
     *
     * @response 204 {}
     */
    public function clear(Request $request): Response
    {
        $this->service->clear($this->user($request));

        return $this->respondNoContent();
    }

    /**
     * The auth:api middleware guarantees a User is authenticated by the
     * time we reach the controller; this helper narrows the request user
     * type for static analysis.
     */
    private function user(Request $request): User
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user;
    }
}
