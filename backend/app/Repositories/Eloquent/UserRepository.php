<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

final class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->query()->where('email', $email)->first();

        return $user;
    }

    public function existsByEmail(string $email): bool
    {
        return $this->query()->where('email', $email)->exists();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): User
    {
        /** @var User $user */
        $user = $this->query()->create($attributes);

        return $user;
    }

    public function find(int $id): ?User
    {
        /** @var User|null $user */
        $user = parent::find($id);

        return $user;
    }

    public function findOrFail(int $id): User
    {
        /** @var User $user */
        $user = parent::findOrFail($id);

        return $user;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
