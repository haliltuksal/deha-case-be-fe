<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

final class LoginRequest extends BaseFormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.string' => 'E-posta adresi geçersiz biçimde.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'password.required' => 'Şifre zorunludur.',
            'password.string' => 'Şifre geçersiz biçimde.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'e-posta',
            'password' => 'şifre',
        ];
    }
}
