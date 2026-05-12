<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Http\Requests\BaseFormRequest;

final class UpdateCartItemRequest extends BaseFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
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
            'quantity' => 'miktar',
        ];
    }
}
