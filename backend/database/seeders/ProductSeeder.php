<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Currency;
use App\Models\Product;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['name' => 'Türk Kahvesi 250g', 'description' => 'Geleneksel Türk kahvesi, taze çekilmiş.', 'price' => '95.00', 'currency' => Currency::TRY, 'stock' => 80],
            ['name' => 'Filtre Kahve Çekirdeği 1kg', 'description' => 'Tek menşeli arabica çekirdek.', 'price' => '420.00', 'currency' => Currency::TRY, 'stock' => 35],
            ['name' => 'Cezve - Bakır El İşi', 'description' => 'Gaziantep ustası imalatı, 4 kişilik.', 'price' => '650.00', 'currency' => Currency::TRY, 'stock' => 18],
            ['name' => 'Espresso Makinesi - Premium', 'description' => 'Profesyonel ev tipi 15 bar pompa.', 'price' => '299.00', 'currency' => Currency::USD, 'stock' => 8],
            ['name' => 'Çikolata Kaplı Lokum 500g', 'description' => 'Antep fıstıklı bitter çikolata kaplı.', 'price' => '180.00', 'currency' => Currency::TRY, 'stock' => 60],
            ['name' => 'Beyaz Porselen Fincan Seti', 'description' => '6 parça espresso fincan seti.', 'price' => '45.00', 'currency' => Currency::EUR, 'stock' => 25],
            ['name' => 'Nargile Set Bohem', 'description' => 'El üfleme cam, gümüş kaplama gövde.', 'price' => '1850.00', 'currency' => Currency::TRY, 'stock' => 5],
            ['name' => 'Türk Çayı 500g', 'description' => 'Rize Çayeli birinci sınıf.', 'price' => '85.00', 'currency' => Currency::TRY, 'stock' => 120],
            ['name' => 'Bakır Kaplama Kahve Termosu', 'description' => 'Çift cidarlı, 500ml.', 'price' => '24.99', 'currency' => Currency::USD, 'stock' => 30],
            ['name' => 'Antep Fıstığı 250g', 'description' => 'Çiğ ham, kabuksuz, A kalite.', 'price' => '320.00', 'currency' => Currency::TRY, 'stock' => 50],
        ];

        foreach ($catalog as $row) {
            Product::query()->updateOrCreate(
                ['name' => $row['name']],
                [
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'base_currency' => $row['currency']->value,
                    'stock' => $row['stock'],
                ],
            );
        }
    }
}
