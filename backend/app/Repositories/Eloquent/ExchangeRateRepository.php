<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ExchangeRateRepositoryInterface;
use App\DTOs\Currency\ExchangeRateData;
use App\Enums\Currency;
use App\Models\ExchangeRate;
use App\Repositories\BaseRepository;

final class ExchangeRateRepository extends BaseRepository implements ExchangeRateRepositoryInterface
{
    public function __construct(ExchangeRate $model)
    {
        parent::__construct($model);
    }

    public function latestForCurrency(Currency $currency): ?ExchangeRate
    {
        /** @var ExchangeRate|null $rate */
        $rate = $this->query()
            ->where('target_currency', $currency->value)
            ->orderByDesc('fetched_at')
            ->first();

        return $rate;
    }

    public function upsert(ExchangeRateData $data): ExchangeRate
    {
        /** @var ExchangeRate $rate */
        $rate = $this->query()->updateOrCreate(
            [
                'target_currency' => $data->currency->value,
                'fetched_at' => $data->fetchedAt,
            ],
            [
                'rate_in_try' => $data->rateInTry,
            ],
        );

        return $rate;
    }
}
