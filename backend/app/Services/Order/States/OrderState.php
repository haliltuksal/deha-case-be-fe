<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Enums\OrderStatus;
use App\Exceptions\Domain\Order\InvalidOrderStateTransitionException;

abstract class OrderState
{
    abstract public function code(): OrderStatus;

    /**
     * Compute the status after a "complete" transition.
     *
     * @throws InvalidOrderStateTransitionException
     */
    public function complete(): OrderStatus
    {
        throw InvalidOrderStateTransitionException::from($this->code(), 'complete');
    }

    /**
     * Compute the status after a "cancel" transition.
     *
     * @throws InvalidOrderStateTransitionException
     */
    public function cancel(): OrderStatus
    {
        throw InvalidOrderStateTransitionException::from($this->code(), 'cancel');
    }

    public static function fromStatus(OrderStatus $status): self
    {
        return match ($status) {
            OrderStatus::PENDING => new PendingState,
            OrderStatus::COMPLETED => new CompletedState,
            OrderStatus::CANCELLED => new CancelledState,
        };
    }
}
