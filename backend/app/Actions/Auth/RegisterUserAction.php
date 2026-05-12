<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\Auth\RegisterUserData;
use App\Models\User;

final readonly class RegisterUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function execute(RegisterUserData $data): User
    {
        return $this->users->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'is_admin' => false,
        ]);
    }
}
