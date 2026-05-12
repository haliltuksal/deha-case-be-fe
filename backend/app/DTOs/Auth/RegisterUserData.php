<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Http\Requests\Auth\RegisterRequest;

final readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
