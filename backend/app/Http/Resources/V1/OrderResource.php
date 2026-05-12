<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Enums\Currency;
use App\Models\Order;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

/**
 * @mixin Order
 */
final class OrderResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $converter = app(CurrencyConverter::class);

        $totals = [];
        foreach (Currency::cases() as $currency) {
            $totals[$currency->value] = $converter->convert(
                amount: $this->total_amount,
                from: Currency::TRY,
                to: $currency,
            );
        }

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'currency' => Currency::TRY->value,
            'totals' => $totals,
            'items' => OrderItemResource::collection($this->items),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
