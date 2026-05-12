<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Enums\Currency;
use App\Models\Product;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

/**
 * @mixin Product
 */
final class ProductResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $converter = app(CurrencyConverter::class);
        $base = $this->base_currency;

        $prices = [];
        foreach (Currency::cases() as $currency) {
            $prices[$currency->value] = $converter->convert(
                amount: $this->price,
                from: $base,
                to: $currency,
            );
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'stock' => $this->stock,
            'base_currency' => $base->value,
            'price' => $prices[$base->value],
            'prices' => $prices,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
