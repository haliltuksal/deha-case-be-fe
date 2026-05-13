<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Enums\Currency;
use App\Models\OrderItem;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

/**
 * @extends BaseResource<OrderItem>
 */
final class OrderItemResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $converter = app(CurrencyConverter::class);

        $display = [];
        foreach (Currency::cases() as $currency) {
            $display[$currency->value] = $converter->convert(
                amount: $this->line_total,
                from: $this->base_currency,
                to: $currency,
            );
        }

        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'unit_price' => $this->unit_price,
            'base_currency' => $this->base_currency->value,
            'quantity' => $this->quantity,
            'line_total' => $this->line_total,
            'line_total_display' => $display,
        ];
    }
}
