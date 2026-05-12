<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the demo admin and a sample
     * non-admin user. Credentials are documented in the README so the
     * reviewer can sign in without going through the registration flow.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@dehasoft.test'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'customer@dehasoft.test'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ],
        );

        $this->call(ProductSeeder::class);
    }
}
