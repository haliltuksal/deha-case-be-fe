<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property numeric-string $unit_price
 * @property Currency $base_currency
 * @property int $quantity
 * @property numeric-string $line_total
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read Order $order
 * @property-read Product|null $product
 */
final class OrderItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'base_currency',
        'quantity',
        'line_total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'base_currency' => Currency::class,
            'quantity' => 'integer',
            'line_total' => 'decimal:2',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
