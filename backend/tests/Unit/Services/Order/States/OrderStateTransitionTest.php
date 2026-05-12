<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Exceptions\Domain\Order\InvalidOrderStateTransitionException;
use App\Services\Order\States\CancelledState;
use App\Services\Order\States\CompletedState;
use App\Services\Order\States\OrderState;
use App\Services\Order\States\PendingState;

it('returns the matching state instance for each status', function (): void {
    expect(OrderState::fromStatus(OrderStatus::PENDING))->toBeInstanceOf(PendingState::class)
        ->and(OrderState::fromStatus(OrderStatus::COMPLETED))->toBeInstanceOf(CompletedState::class)
        ->and(OrderState::fromStatus(OrderStatus::CANCELLED))->toBeInstanceOf(CancelledState::class);
});

it('lets a pending order complete to completed', function (): void {
    expect((new PendingState)->complete())->toBe(OrderStatus::COMPLETED);
});

it('lets a pending order cancel to cancelled', function (): void {
    expect((new PendingState)->cancel())->toBe(OrderStatus::CANCELLED);
});

it('refuses to complete a completed order', function (): void {
    expect(fn () => (new CompletedState)->complete())
        ->toThrow(InvalidOrderStateTransitionException::class);
});

it('refuses to cancel a completed order', function (): void {
    expect(fn () => (new CompletedState)->cancel())
        ->toThrow(InvalidOrderStateTransitionException::class);
});

it('refuses to complete a cancelled order', function (): void {
    expect(fn () => (new CancelledState)->complete())
        ->toThrow(InvalidOrderStateTransitionException::class);
});

it('refuses to cancel a cancelled order', function (): void {
    expect(fn () => (new CancelledState)->cancel())
        ->toThrow(InvalidOrderStateTransitionException::class);
});

it('embeds the current status and attempted action in the exception details', function (): void {
    try {
        (new CompletedState)->cancel();
        expect(false)->toBeTrue('expected exception was not thrown');
    } catch (InvalidOrderStateTransitionException $e) {
        expect($e->getStatusCode())->toBe(422)
            ->and($e->getErrorCode())->toBe('ERR_INVALID_ORDER_TRANSITION')
            ->and($e->getDetails())->toBe([
                'current_status' => 'completed',
                'attempted_action' => 'cancel',
            ]);
    }
});
