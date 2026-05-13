<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Models\Order;

final readonly class OrderStateTransitioner
{
    public function complete(Order $order): void
    {
        $order->status = OrderState::fromStatus($order->status)->complete();
        $order->save();
    }

    public function cancel(Order $order): void
    {
        $order->status = OrderState::fromStatus($order->status)->cancel();
        $order->save();
    }
}
