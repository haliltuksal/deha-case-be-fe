<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Exceptions\Domain\Order\InvalidOrderStateTransitionException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Order\States\OrderStateTransitioner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cancel an order owned by the user. Returns stock to every line item's
 * referenced product (skipping any whose product was deleted via the
 * SET NULL cascade) and applies the state transition under one
 * transaction so stock and status stay consistent.
 */
final readonly class CancelOrderAction
{
    public function __construct(
        private OrderRepositoryInterface $orders,
        private OrderStateTransitioner $transitioner,
    ) {}

    /**
     * @throws ModelNotFoundException when the order does not belong to the user
     * @throws InvalidOrderStateTransitionException when the order is not in a cancellable state
     */
    public function execute(User $user, int $orderId): Order
    {
        return DB::transaction(function () use ($user, $orderId): Order {
            $order = $this->orders->findForUser($user, $orderId);

            if ($order === null) {
                throw (new ModelNotFoundException)->setModel(Order::class);
            }

            $order->refresh();

            $items = $order->items()->lockForUpdate()->get();

            foreach ($items as $item) {
                if ($item->product_id === null) {
                    continue;
                }

                /** @var Product|null $product */
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                $product?->increment('stock', $item->quantity);
            }

            $this->transitioner->cancel($order);

            Log::channel('order')->info('Order cancelled and stock returned.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'items_restored' => $items->whereNotNull('product_id')->count(),
            ]);

            return $this->orders->loadItemsWithProducts($order);
        });
    }
}
