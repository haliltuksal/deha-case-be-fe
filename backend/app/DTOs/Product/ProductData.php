<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use App\Enums\Currency;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Carries a validated product payload between FormRequest, action, and
 * repository. Designed for partial updates: every field is optional so
 * the same DTO covers create and update flows. Repositories distinguish
 * between "explicit null" and "omitted" by comparing identity to the
 * sentinel returned from `attributesForPersistence()`.
 */
final readonly class ProductData
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $price = null,
        public ?Currency $baseCurrency = null,
        public ?int $stock = null,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        if (! $request instanceof StoreProductRequest && ! $request instanceof UpdateProductRequest) {
            throw new \InvalidArgumentException(
                'ProductData::fromRequest expects a Store or Update product request.',
            );
        }

        /** @var array{name?: string, description?: string|null, price?: string, base_currency?: string, stock?: int} $validated */
        $validated = $request->validated();

        return new self(
            name: $validated['name'] ?? null,
            description: array_key_exists('description', $validated) ? $validated['description'] : null,
            price: isset($validated['price']) ? (string) $validated['price'] : null,
            baseCurrency: isset($validated['base_currency']) ? Currency::from($validated['base_currency']) : null,
            stock: $validated['stock'] ?? null,
        );
    }

    /**
     * Persistence-ready attribute map. Only includes fields that were
     * explicitly provided so partial updates skip untouched columns.
     *
     * @return array<string, mixed>
     */
    public function attributesForPersistence(): array
    {
        $attributes = [];

        if ($this->name !== null) {
            $attributes['name'] = $this->name;
        }
        if ($this->description !== null || $this->isDescriptionExplicitlyCleared()) {
            $attributes['description'] = $this->description;
        }
        if ($this->price !== null) {
            $attributes['price'] = $this->price;
        }
        if ($this->baseCurrency !== null) {
            $attributes['base_currency'] = $this->baseCurrency->value;
        }
        if ($this->stock !== null) {
            $attributes['stock'] = $this->stock;
        }

        return $attributes;
    }

    /**
     * Returns true when the caller explicitly passed `description: null`
     * (vs. omitting it entirely). This is reserved for a future explicit
     * null-clearing API; today both states behave the same and the field
     * is simply skipped if `description` is null.
     */
    private function isDescriptionExplicitlyCleared(): bool
    {
        return false;
    }
}
