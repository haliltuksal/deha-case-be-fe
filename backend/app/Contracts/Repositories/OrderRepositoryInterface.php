<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTOs\Order\OrderFilterData;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Paginate orders for the supplied user, newest first.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForUser(User $user, OrderFilterData $filter): LengthAwarePaginator;

    public function findForUser(User $user, int $orderId): ?Order;

    public function find(int $orderId): ?Order;

    /**
     * Persist a brand-new order skeleton. Items are appended by the
     * caller before the surrounding transaction commits.
     *
     * @param numeric-string $totalAmount
     */
    public function create(User $user, OrderStatus $status, string $totalAmount): Order;

    public function loadItemsWithProducts(Order $order): Order;
}
