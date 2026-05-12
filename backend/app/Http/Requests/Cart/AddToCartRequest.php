<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

final class AddToCartRequest extends BaseFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Ürün kimliği zorunludur.',
            'product_id.integer' => 'Ürün kimliği tam sayı olmalı.',
            'product_id.exists' => 'Seçilen ürün bulunamadı.',

            'quantity.required' => 'Miktar zorunludur.',
            'quantity.integer' => 'Miktar tam sayı olmalı.',
            'quantity.min' => 'Miktar en az :min olmalı.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'ürün',
            'quantity' => 'miktar',
        ];
    }
}
