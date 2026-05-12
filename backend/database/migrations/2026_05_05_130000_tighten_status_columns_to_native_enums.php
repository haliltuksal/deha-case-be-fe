<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replaces the loose `string(20)` / `string(3)` columns with native MySQL
 * ENUMs so the database refuses to store any value the application's enums
 * cannot represent. The PHP `OrderStatus` and `Currency` enum casts already
 * gave us type safety inside the Eloquent model, but the column stayed
 * permissive — a stray migration or direct-SQL fix could plant data the
 * model could not read. This migration closes that gap.
 *
 * Skipped on SQLite (in-memory test driver) because SQLite has no native
 * ENUM type. Tests already exercise the PHP-level enum cast which catches
 * invalid values before they reach the database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'completed', 'cancelled') NOT NULL");
        DB::statement("ALTER TABLE products MODIFY base_currency ENUM('TRY', 'USD', 'EUR') NOT NULL");
        DB::statement("ALTER TABLE order_items MODIFY base_currency ENUM('TRY', 'USD', 'EUR') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE orders MODIFY status VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE products MODIFY base_currency VARCHAR(3) NOT NULL');
        DB::statement('ALTER TABLE order_items MODIFY base_currency VARCHAR(3) NOT NULL');
    }
};
