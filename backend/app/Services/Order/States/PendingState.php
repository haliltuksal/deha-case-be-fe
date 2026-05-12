<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Enums\OrderStatus;

final class PendingState extends OrderState
{
    public function code(): OrderStatus
    {
        return OrderStatus::PENDING;
    }

    public function complete(): OrderStatus
    {
        return OrderStatus::COMPLETED;
    }

    public function cancel(): OrderStatus
    {
        return OrderStatus::CANCELLED;
    }
}
