<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;

final class ListOrdersRequest extends BaseFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => 'Sayfa başına kayıt sayısı tam sayı olmalı.',
            'per_page.min' => 'Sayfa başına en az :min kayıt olmalı.',
            'per_page.max' => 'Sayfa başına en fazla :max kayıt olabilir.',

            'page.integer' => 'Sayfa numarası tam sayı olmalı.',
            'page.min' => 'Sayfa numarası en az :min olmalı.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'per_page' => 'sayfa başına kayıt',
            'page' => 'sayfa',
        ];
    }
}
