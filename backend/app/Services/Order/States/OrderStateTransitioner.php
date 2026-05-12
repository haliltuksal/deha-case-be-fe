<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Models\Order;

/**
 * Couples the pure State decision to persistence: applies the next
 * status returned by the State pattern and saves the model. Throws
 * InvalidOrderStateTransitionException through the State if the
 * requested transition is not allowed from the order's current status.
 */
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
