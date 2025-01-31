<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call individual seeders
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            LocationSeeder::class,
            IconsSeeder::class,
            PropertyTypeSeeder::class,
            PropertySeeder::class,
            UserDetailSeeder::class,
            UserReviewSeeder::class,
            BookingSeeder::class,
            InvoiceSeeder::class,
            PaymentMethodSeeder::class,
            PaymentSeeder::class,
            PayoutOptionSeeder::class,
            LanguageSeeder::class,
            CurrencySeeder::class,
            StaySeeder::class
        ]);
    }
}
