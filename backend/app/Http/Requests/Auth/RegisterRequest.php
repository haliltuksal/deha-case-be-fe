<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegisterRequest extends BaseFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ad soyad zorunludur.',
            'name.string' => 'Ad soyad metin olmalı.',
            'name.min' => 'Ad soyad en az :min karakter olmalı.',
            'name.max' => 'Ad soyad en fazla :max karakter olabilir.',

            'email.required' => 'E-posta adresi zorunludur.',
            'email.string' => 'E-posta adresi geçersiz biçimde.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.max' => 'E-posta adresi en fazla :max karakter olabilir.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',

            'password.required' => 'Şifre zorunludur.',
            'password.string' => 'Şifre geçersiz biçimde.',
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
            'password.min' => 'Şifre en az :min karakter olmalı.',
            'password.letters' => 'Şifre en az bir harf içermeli.',
            'password.numbers' => 'Şifre en az bir rakam içermeli.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'ad soyad',
            'email' => 'e-posta',
            'password' => 'şifre',
            'password_confirmation' => 'şifre tekrarı',
        ];
    }
}
