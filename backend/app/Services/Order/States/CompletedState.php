<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Enums\OrderStatus;

final class CompletedState extends OrderState
{
    public function code(): OrderStatus
    {
        return OrderStatus::COMPLETED;
    }

    // complete() and cancel() inherit the throwing default — completed
    // is a terminal state.
}
