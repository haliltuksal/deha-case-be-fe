<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Enums\Currency;
use App\Models\CartItem;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

/**
 * @mixin CartItem
 */
final class CartItemResource extends BaseResource
{
    /**
     * Optional precomputed per-currency subtotal supplied by the parent
     * `CartResource` so the same conversions are not repeated in both the
     * aggregate totals loop and the per-item loop. When absent (e.g. the
     * resource is rendered standalone) the subtotal is computed locally.
     *
     * @var array<string, numeric-string>|null
     */
    public ?array $subtotalOverride = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $base = $product->base_currency;

        $subtotal = $this->subtotalOverride ?? $this->computeSubtotal();

        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => $this->quantity,
            'stock_available' => $product->stock,
            'unit_price' => $product->price,
            'unit_currency' => $base->value,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * @return array<string, numeric-string>
     */
    private function computeSubtotal(): array
    {
        $product = $this->product;
        $base = $product->base_currency;
        $converter = app(CurrencyConverter::class);

        $lineTotalInBase = bcmul($product->price, (string) $this->quantity, 2);

        $subtotal = [];
        foreach (Currency::cases() as $currency) {
            $subtotal[$currency->value] = $converter->convert($lineTotalInBase, $base, $currency);
        }

        return $subtotal;
    }
}
