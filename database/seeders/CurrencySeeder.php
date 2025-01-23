<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            // African Currencies
            ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'K', 'is_active' => true],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'is_active' => true],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'is_active' => true],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => '£', 'is_active' => true],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => '₵', 'is_active' => true],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'is_active' => true],
            ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'is_active' => true],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'د.ج', 'is_active' => true],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'د.م.', 'is_active' => true],
            ['code' => 'XAF', 'name' => 'Central African CFA Franc', 'symbol' => 'FCFA', 'is_active' => true],

            // Asian Currencies
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'is_active' => true],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'is_active' => true],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'is_active' => true],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩', 'is_active' => true],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱', 'is_active' => true],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'is_active' => true],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'is_active' => true],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'is_active' => true],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'is_active' => true],
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'is_active' => true],

            // European Currencies
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'is_active' => true],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'is_active' => true],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'is_active' => true],
            ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'is_active' => true],
            ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'is_active' => true],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'is_active' => true],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'is_active' => true],

            // American Currencies
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$', 'is_active' => true],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'is_active' => true],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => 'MX$', 'is_active' => true],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'is_active' => true],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'is_active' => true],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => 'CLP$', 'is_active' => true],
            ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => 'COL$', 'is_active' => true],
            ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'is_active' => true],
            ['code' => 'UYU', 'name' => 'Uruguayan Peso', 'symbol' => '$U', 'is_active' => true],
            ['code' => 'VEF', 'name' => 'Venezuelan Bolívar', 'symbol' => 'Bs.', 'is_active' => true],

            // Oceanian Currencies
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'is_active' => true],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'is_active' => true],
            ['code' => 'FJD', 'name' => 'Fijian Dollar', 'symbol' => 'FJ$', 'is_active' => true],
            ['code' => 'PGK', 'name' => 'Papua New Guinean Kina', 'symbol' => 'K', 'is_active' => true],
            ['code' => 'SBD', 'name' => 'Solomon Islands Dollar', 'symbol' => 'SI$', 'is_active' => true],
        ];


        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
