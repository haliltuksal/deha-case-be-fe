<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('base_currency', 3);
            $table->unsignedInteger('stock');
            $table->timestamps();

            $table->index('name');
            $table->index('base_currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
