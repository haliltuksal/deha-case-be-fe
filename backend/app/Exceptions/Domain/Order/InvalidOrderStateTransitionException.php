<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Order;

use App\Enums\OrderStatus;
use App\Support\Http\ApiException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Raised when a state transition is requested that the current state
 * does not permit (e.g. cancelling an already-completed order).
 */
final class InvalidOrderStateTransitionException extends ApiException
{
    public static function from(OrderStatus $current, string $attemptedAction): self
    {
        return new self(
            message: sprintf(
                'Cannot %s an order whose current status is %s.',
                $attemptedAction,
                $current->value,
            ),
            details: [
                'current_status' => $current->value,
                'attempted_action' => $attemptedAction,
            ],
        );
    }

    public function getStatusCode(): int
    {
        return HttpResponse::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrorCode(): string
    {
        return 'ERR_INVALID_ORDER_TRANSITION';
    }
}
