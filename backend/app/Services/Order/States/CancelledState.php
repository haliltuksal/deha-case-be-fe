<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Enums\OrderStatus;

final class CancelledState extends OrderState
{
    public function code(): OrderStatus
    {
        return OrderStatus::CANCELLED;
    }

    // complete() and cancel() inherit the throwing default — cancelled
    // is a terminal state.
}
