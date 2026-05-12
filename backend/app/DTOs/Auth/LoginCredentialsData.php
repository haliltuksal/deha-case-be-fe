<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\LoginRequest;

final readonly class LoginCredentialsData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        /** @var array{email: string, password: string} $validated */
        $validated = $request->validated();

        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }

    /**
     * Shape expected by Laravel's auth attempt() / JWT guard.
     *
     * @return array{email: string, password: string}
     */
    public function toCredentialsArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
