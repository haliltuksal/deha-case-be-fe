<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Carbon\CarbonImmutable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property numeric-string $price
 * @property Currency $base_currency
 * @property int $stock
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'base_currency',
        'stock',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'base_currency' => Currency::class,
            'stock' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
