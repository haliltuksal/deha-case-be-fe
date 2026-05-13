<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CompleteOrderAction;
use App\Actions\Order\CreateOrderFromCartAction;
use App\Actions\Order\ListUserOrdersAction;
use App\Actions\Order\ShowOrderAction;
use App\DTOs\Order\OrderFilterData;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class OrderService
{
    public function __construct(
        private CreateOrderFromCartAction $createAction,
        private ListUserOrdersAction $listAction,
        private ShowOrderAction $showAction,
        private CancelOrderAction $cancelAction,
        private CompleteOrderAction $completeAction,
    ) {}

    public function createFromCart(User $user): Order
    {
        return $this->createAction->execute($user);
    }

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function list(User $user, OrderFilterData $filter): LengthAwarePaginator
    {
        return $this->listAction->execute($user, $filter);
    }

    public function show(User $user, int $orderId): Order
    {
        return $this->showAction->execute($user, $orderId);
    }

    public function cancel(User $user, int $orderId): Order
    {
        return $this->cancelAction->execute($user, $orderId);
    }

    public function complete(int $orderId): Order
    {
        return $this->completeAction->execute($orderId);
    }
}
