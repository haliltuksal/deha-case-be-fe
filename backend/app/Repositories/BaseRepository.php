<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Eloquent repository scaffold. Concrete repositories extend this
 * class for the model-agnostic helpers (`find`, `findOrFail`, `all`, plus
 * the protected `query()` builder).
 *
 * Domain-specific operations — pagination filters, typed creates, deletes
 * with narrowed parameter types — live on the concrete classes so each
 * one declares exactly the contract its interface requires, without
 * fighting PHP's parameter contravariance rules.
 */
abstract class BaseRepository
{
    public function __construct(
        protected Model $model,
    ) {}

    public function find(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    /**
     * @return Collection<int, Model>
     */
    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    /**
     * Fresh query builder against the underlying model — concrete repos
     * compose criteria on top of this.
     *
     * @return Builder<Model>
     */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}
