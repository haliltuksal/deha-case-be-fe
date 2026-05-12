<?php

declare(strict_types=1);

namespace App\Services\Order\States;

use App\Enums\OrderStatus;
use App\Exceptions\Domain\Order\InvalidOrderStateTransitionException;

/**
 * Abstract State for the Order aggregate. Concrete subclasses encode the
 * transitions allowed from their current status. Default implementations
 * raise InvalidOrderStateTransitionException; subclasses override only
 * the transitions they permit.
 *
 * The state objects are pure: they decide *which* status comes next, but
 * never touch the database. The OrderStateTransitioner couples the state
 * decision to persistence.
 */
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

    /**
     * Map an enum value to its concrete state instance.
     */
    public static function fromStatus(OrderStatus $status): self
    {
        return match ($status) {
            OrderStatus::PENDING => new PendingState,
            OrderStatus::COMPLETED => new CompletedState,
            OrderStatus::CANCELLED => new CancelledState,
        };
    }
}
