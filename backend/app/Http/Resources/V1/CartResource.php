<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Enums\Currency;
use App\Models\Cart;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

/**
 * @mixin Cart
 */
final class CartResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $converter = app(CurrencyConverter::class);
        $items = $this->items;

        $totals = ['TRY' => '0.00', 'USD' => '0.00', 'EUR' => '0.00'];
        $totalQuantity = 0;
        // Map of cart_item id => per-currency subtotal, computed once and
        // shared with each CartItemResource so the conversion does not
        // re-run when each child renders.
        $subtotalsByItemId = [];

        foreach ($items as $item) {
            $product = $item->product;
            $base = $product->base_currency;
            $lineTotalInBase = bcmul($product->price, (string) $item->quantity, 2);
            $totalQuantity += $item->quantity;

            $subtotal = [];
            foreach (Currency::cases() as $currency) {
                $converted = $converter->convert($lineTotalInBase, $base, $currency);
                $subtotal[$currency->value] = $converted;
                $totals[$currency->value] = bcadd($totals[$currency->value], $converted, 2);
            }
            $subtotalsByItemId[$item->id] = $subtotal;
        }

        $itemResources = $items->map(function ($item) use ($subtotalsByItemId): CartItemResource {
            $resource = new CartItemResource($item);
            $resource->subtotalOverride = $subtotalsByItemId[$item->id] ?? null;

            return $resource;
        });

        return [
            'id' => $this->id,
            'items' => $itemResources->map(fn (CartItemResource $r): array => $r->toArray($request))->all(),
            'totals' => $totals,
            'item_count' => $items->count(),
            'total_quantity' => $totalQuantity,
        ];
    }
}
