<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
