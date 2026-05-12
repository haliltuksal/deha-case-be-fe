<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Order\OrderFilterData;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class OrderRepository implements OrderRepositoryInterface
{
    public function paginateForUser(User $user, OrderFilterData $filter): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Order> $paginator */
        $paginator = Order::query()
            ->where('user_id', $user->id)
            ->with('items')
            ->orderByDesc('id')
            ->paginate(perPage: $filter->perPage, page: $filter->page);

        return $paginator;
    }

    public function findForUser(User $user, int $orderId): ?Order
    {
        /** @var Order|null $order */
        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->first();

        return $order;
    }

    public function find(int $orderId): ?Order
    {
        /** @var Order|null $order */
        $order = Order::query()->find($orderId);

        return $order;
    }

    public function create(User $user, OrderStatus $status, string $totalAmount): Order
    {
        /** @var Order $order */
        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => $status->value,
            'total_amount' => $totalAmount,
        ]);

        return $order;
    }

    public function loadItemsWithProducts(Order $order): Order
    {
        return $order->load(['items.product']);
    }
}
