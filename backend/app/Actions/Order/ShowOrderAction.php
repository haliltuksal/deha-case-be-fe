<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class ShowOrderAction
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    /**
     * @throws ModelNotFoundException when the order does not exist or
     *                                belongs to a different user
     */
    public function execute(User $user, int $orderId): Order
    {
        $order = $this->orders->findForUser($user, $orderId);

        if ($order === null) {
            throw (new ModelNotFoundException)->setModel(Order::class);
        }

        return $this->orders->loadItemsWithProducts($order);
    }
}
