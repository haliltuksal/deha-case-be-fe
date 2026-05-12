<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Order\OrderFilterData;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListUserOrdersAction
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function execute(User $user, OrderFilterData $filter): LengthAwarePaginator
    {
        return $this->orders->paginateForUser($user, $filter);
    }
}
