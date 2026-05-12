<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Carbon\CarbonImmutable;
use Database\Factories\ExchangeRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Currency $target_currency
 * @property string $rate_in_try
 * @property CarbonImmutable $fetched_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class ExchangeRate extends Model
{
    /** @use HasFactory<ExchangeRateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'target_currency',
        'rate_in_try',
        'fetched_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_currency' => Currency::class,
            'rate_in_try' => 'decimal:8',
            'fetched_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
