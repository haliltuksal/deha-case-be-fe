<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\CartCacheRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Exceptions\Domain\Cart\InsufficientStockException;
use App\Exceptions\Domain\Order\EmptyCartException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Atomic checkout: read the cart with FOR UPDATE locks, snapshot every
 * line into order_items, decrement stock, persist the order header, and
 * clear the cart. Either every side-effect commits or none of them do —
 * the surrounding DB transaction guarantees no half-applied state.
 */
final readonly class CreateOrderFromCartAction
{
    public function __construct(
        private CartRepositoryInterface $carts,
        private OrderRepositoryInterface $orders,
        private CurrencyConverter $converter,
        private CartCacheRepositoryInterface $cache,
    ) {}

    /**
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
    public function execute(User $user): Order
    {
        $lock = $this->cache->lock($user->id);

        return $lock->block(5, fn (): Order => DB::transaction(function () use ($user): Order {
            $cart = $this->carts->findOrCreateForUser($user);

            $items = $cart->items()
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new EmptyCartException;
            }

            $totalAmount = '0.00';
            $orderItemRows = [];

            foreach ($items as $item) {
                /** @var Product $product */
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw InsufficientStockException::for(
                        productId: $product->id,
                        requested: $item->quantity,
                        available: $product->stock,
                    );
                }

                $lineTotalInBase = bcmul($product->price, (string) $item->quantity, 2);
                assert(is_numeric($lineTotalInBase));

                $lineInTry = $this->converter->convert(
                    amount: $lineTotalInBase,
                    from: $product->base_currency,
                    to: Currency::TRY,
                );

                $totalAmount = bcadd($totalAmount, $lineInTry, 2);

                $orderItemRows[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'base_currency' => $product->base_currency->value,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotalInBase,
                ];

                $product->decrement('stock', $item->quantity);
            }

            assert(is_numeric($totalAmount));
            $order = $this->orders->create($user, OrderStatus::PENDING, $totalAmount);
            $order->items()->createMany($orderItemRows);

            $this->carts->clearItems($cart);

            // Tie the cache invalidation to the transaction commit so a
            // rollback (insufficient stock raised mid-loop, etc.) cannot
            // wipe Redis while MySQL still holds the live cart.
            DB::afterCommit(fn () => $this->cache->forget($user->id));

            Log::channel('order')->info('Order created from cart.', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'items' => count($orderItemRows),
            ]);

            return $this->orders->loadItemsWithProducts($order);
        }));
    }
}
