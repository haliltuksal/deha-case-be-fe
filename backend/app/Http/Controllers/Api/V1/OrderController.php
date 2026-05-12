<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Order\OrderFilterData;
use App\Http\Requests\Order\ListOrdersRequest;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\PaginatedOrderCollection;
use App\Models\User;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends BaseApiController
{
    public function __construct(
        private readonly OrderService $service,
    ) {}

    /**
     * List my orders
     *
     * Paginated list of the authenticated user's orders, newest first.
     * Other users' orders are never visible.
     *
     * @group Orders
     *
     * @authenticated
     *
     * @queryParam per_page integer Page size, 1–100 (default 15). Example: 15
     * @queryParam page integer Page number (default 1). Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "items": [
     *       {
     *         "id": 5,
     *         "status": "pending",
     *         "total_amount": "350.00",
     *         "currency": "TRY",
     *         "totals": {"TRY": "350.00", "USD": "7.75", "EUR": "6.61"},
     *         "items": [
     *           {"product_id": 1, "product_name": "Türk Kahvesi 250g", "unit_price": "95.00", "base_currency": "TRY", "quantity": 2, "line_total": "190.00", "line_total_display": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"}}
     *         ],
     *         "created_at": "2026-05-04T20:00:00+00:00",
     *         "updated_at": "2026-05-04T20:00:00+00:00"
     *       }
     *     ],
     *     "pagination": {"current_page": 1, "last_page": 1, "per_page": 15, "total": 1}
     *   }
     * }
     */
    public function index(ListOrdersRequest $request): JsonResponse
    {
        $paginator = $this->service->list($this->user($request), OrderFilterData::fromRequest($request));

        return $this->respondOk(new PaginatedOrderCollection($paginator));
    }

    /**
     * Create order from cart
     *
     * Atomic checkout. Reads the cart with FOR UPDATE locks, snapshots
     * each line into order_items, decrements stock on the referenced
     * products, persists the order with status PENDING, and clears the
     * cart — all in one DB transaction. The order's `total_amount` is
     * stored in TRY (canonical); display currencies are computed live
     * via the cached TCMB rates.
     *
     * @group Orders
     *
     * @authenticated
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Sipariş #5 oluşturuldu.",
     *   "data": {
     *     "id": 5,
     *     "status": "pending",
     *     "total_amount": "350.00",
     *     "currency": "TRY",
     *     "totals": {"TRY": "350.00", "USD": "7.75", "EUR": "6.61"},
     *     "items": [{"product_id": 1, "product_name": "Türk Kahvesi 250g", "unit_price": "95.00", "base_currency": "TRY", "quantity": 2, "line_total": "190.00", "line_total_display": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"}}],
     *     "created_at": "2026-05-04T20:00:00+00:00",
     *     "updated_at": "2026-05-04T20:00:00+00:00"
     *   }
     * }
     * @response 422 scenario="empty cart" {
     *   "status": "error",
     *   "message": "Cannot create an order from an empty cart.",
     *   "data": null,
     *   "code": "ERR_EMPTY_CART"
     * }
     * @response 422 scenario="stock changed since cart-add" {
     *   "status": "error",
     *   "message": "Requested quantity exceeds the available stock.",
     *   "data": null,
     *   "code": "ERR_INSUFFICIENT_STOCK",
     *   "details": {"product_id": 1, "requested": 5, "available": 3}
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $order = $this->service->createFromCart($this->user($request));

        return $this->respondCreated(
            OrderResource::make($order),
            location: route('orders.show', ['order' => $order->id]),
            message: "Sipariş #{$order->id} oluşturuldu.",
        );
    }

    /**
     * Show order
     *
     * Fetch one order belonging to the authenticated user. Cross-user
     * lookups return 404 (existence is deliberately undisclosed).
     *
     * @group Orders
     *
     * @authenticated
     *
     * @urlParam order integer required Order id. Example: 5
     *
     * @response 200 {
     *   "status": "success",
     *   "message": null,
     *   "data": {
     *     "id": 5,
     *     "status": "pending",
     *     "total_amount": "350.00",
     *     "currency": "TRY",
     *     "totals": {"TRY": "350.00", "USD": "7.75", "EUR": "6.61"},
     *     "items": [{"product_id": 1, "product_name": "Türk Kahvesi 250g", "unit_price": "95.00", "base_currency": "TRY", "quantity": 2, "line_total": "190.00", "line_total_display": {"TRY": "190.00", "USD": "4.20", "EUR": "3.59"}}],
     *     "created_at": "2026-05-04T20:00:00+00:00",
     *     "updated_at": "2026-05-04T20:00:00+00:00"
     *   }
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "The requested resource was not found.",
     *   "data": null,
     *   "code": "ERR_NOT_FOUND"
     * }
     */
    public function show(Request $request, int $order): JsonResponse
    {
        return $this->respondOk(
            OrderResource::make($this->service->show($this->user($request), $order)),
        );
    }

    /**
     * Cancel order
     *
     * Cancel a PENDING order owned by the authenticated user. Stock is
     * returned to every line's referenced product (lines whose product
     * was deleted are skipped — those stock units stay where they are).
     * Already-completed or already-cancelled orders return 422.
     *
     * @group Orders
     *
     * @authenticated
     *
     * @urlParam order integer required Order id. Example: 5
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Sipariş iptal edildi.",
     *   "data": {
     *     "id": 5,
     *     "status": "cancelled",
     *     "total_amount": "350.00",
     *     "currency": "TRY",
     *     "totals": {"TRY": "350.00", "USD": "7.75", "EUR": "6.61"},
     *     "items": [],
     *     "created_at": "2026-05-04T20:00:00+00:00",
     *     "updated_at": "2026-05-04T20:01:00+00:00"
     *   }
     * }
     * @response 422 scenario="terminal state" {
     *   "status": "error",
     *   "message": "Cannot cancel an order whose current status is completed.",
     *   "data": null,
     *   "code": "ERR_INVALID_ORDER_TRANSITION",
     *   "details": {"current_status": "completed", "attempted_action": "cancel"}
     * }
     */
    public function cancel(Request $request, int $order): JsonResponse
    {
        return $this->respondOk(
            OrderResource::make($this->service->cancel($this->user($request), $order)),
            message: 'Sipariş iptal edildi.',
        );
    }

    /**
     * Complete order (admin)
     *
     * Move a PENDING order to COMPLETED. Stock has already been
     * decremented at order-creation time so completion is purely a
     * status mutation. Requires an admin bearer token.
     *
     * @group Orders
     *
     * @authenticated
     *
     * @urlParam order integer required Order id. Example: 5
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Sipariş tamamlandı.",
     *   "data": {
     *     "id": 5,
     *     "status": "completed",
     *     "total_amount": "350.00",
     *     "currency": "TRY",
     *     "totals": {"TRY": "350.00", "USD": "7.75", "EUR": "6.61"},
     *     "items": [],
     *     "created_at": "2026-05-04T20:00:00+00:00",
     *     "updated_at": "2026-05-04T20:02:00+00:00"
     *   }
     * }
     * @response 403 scenario="not an admin" {
     *   "status": "error",
     *   "message": "Administrator privileges are required.",
     *   "data": null,
     *   "code": "ERR_UNAUTHORIZED"
     * }
     * @response 422 scenario="terminal state" {
     *   "status": "error",
     *   "message": "Cannot complete an order whose current status is cancelled.",
     *   "data": null,
     *   "code": "ERR_INVALID_ORDER_TRANSITION",
     *   "details": {"current_status": "cancelled", "attempted_action": "complete"}
     * }
     */
    public function complete(Request $request, int $order): JsonResponse
    {
        return $this->respondOk(
            OrderResource::make($this->service->complete($order)),
            message: 'Sipariş tamamlandı.',
        );
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user;
    }
}
