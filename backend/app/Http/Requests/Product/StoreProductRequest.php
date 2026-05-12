<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\Currency;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

final class StoreProductRequest extends BaseFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'base_currency' => ['required', 'string', Rule::enum(Currency::class)],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ürün adı zorunludur.',
            'name.string' => 'Ürün adı metin olmalı.',
            'name.min' => 'Ürün adı en az :min karakter olmalı.',
            'name.max' => 'Ürün adı en fazla :max karakter olabilir.',

            'description.string' => 'Açıklama metin olmalı.',
            'description.max' => 'Açıklama en fazla :max karakter olabilir.',

            'price.required' => 'Fiyat zorunludur.',
            'price.numeric' => 'Fiyat sayısal olmalı.',
            'price.decimal' => 'Fiyat en fazla 2 ondalık basamak içerebilir.',
            'price.min' => 'Fiyat negatif olamaz.',

            'base_currency.required' => 'Para birimi zorunludur.',
            'base_currency.string' => 'Para birimi metin olmalı.',
            'base_currency.enum' => 'Para birimi yalnızca TRY, USD veya EUR olabilir.',

            'stock.required' => 'Stok zorunludur.',
            'stock.integer' => 'Stok tam sayı olmalı.',
            'stock.min' => 'Stok negatif olamaz.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ürün adı',
            'description' => 'açıklama',
            'price' => 'fiyat',
            'base_currency' => 'para birimi',
            'stock' => 'stok',
        ];
    }
}
