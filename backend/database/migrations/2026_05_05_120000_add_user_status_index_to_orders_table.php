<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a composite index on `(user_id, status)` to support the natural
 * "this user's pending / completed / cancelled orders" filter pattern.
 *
 * The base `(user_id, created_at)` composite already covers the unfiltered
 * paginated list; this additional index pays for itself the moment the
 * /orders endpoint accepts a `?status=` filter.
 *
 * Other indexes that already exist on this schema (kept here for context):
 *   - users.email                 unique           (auth lookup)
 *   - users.is_admin              btree            (admin filtering)
 *   - carts.user_id               unique           (one cart per user)
 *   - cart_items.(cart_id, ..)    unique composite (idempotent add-to-cart)
 *   - orders.(user_id, created_at) composite       (paginated user history)
 *   - orders.status               btree            (admin status filtering)
 *   - order_items.order_id        btree            (line-item join)
 *   - order_items.product_id      auto (FK)        (product → orders trace)
 *   - products.name               btree            (catalog search)
 *   - products.base_currency      btree            (currency filtering)
 *   - exchange_rates.(target_currency, fetched_at) unique composite
 *                                                  (latest rate per currency)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['user_id', 'status'], 'orders_user_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_user_id_status_index');
        });
    }
};
