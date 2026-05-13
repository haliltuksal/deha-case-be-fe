<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Exceptions\Domain\Order\InvalidOrderStateTransitionException;
use App\Models\Order;
use App\Services\Order\States\OrderStateTransitioner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class CompleteOrderAction
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderStateTransitioner $transitioner,
    ) {}

    /**
     * @throws ModelNotFoundException
     * @throws InvalidOrderStateTransitionException
     */
    public function execute(int $orderId): Order
    {
        return DB::transaction(function () use ($orderId): Order {
            $order = $this->orders->find($orderId);

            if ($order === null) {
                throw (new ModelNotFoundException)->setModel(Order::class);
            }

            $order->refresh();
            $this->transitioner->complete($order);

            Log::channel('order')->info('Order marked as completed.', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);

            return $this->orders->loadItemsWithProducts($order);
        });
    }
}
