<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function existsByEmail(string $email): bool;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): User;
}
