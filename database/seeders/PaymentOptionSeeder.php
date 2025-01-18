<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
            $paymentOptions = [
                [
                    'name' => 'Credit Card',
                    'description' => 'Pay using credit or debit cards.',
                    'icon' => 'credit_card_icon.png',
                    'icon_alt' => 'Credit Card Icon',
                    'country_code' => 'US',
                    'currency' => 'USD',
                    'link' => 'https://example.com/credit-card',
                ],
                [
                    'name' => 'PayPal',
                    'description' => 'Pay easily with your PayPal account.',
                    'icon' => 'paypal_icon.png',
                    'icon_alt' => 'PayPal Icon',
                    'country_code' => 'US',
                    'currency' => 'USD',
                    'link' => 'https://example.com/paypal',
                ],
                [
                    'name' => 'Bank Transfer',
                    'description' => 'Transfer directly from your bank account.',
                    'icon' => 'bank_transfer_icon.png',
                    'icon_alt' => 'Bank Transfer Icon',
                    'country_code' => 'UK',
                    'currency' => 'GBP',
                    'link' => 'https://example.com/bank-transfer',
                ],
                [
                    'name' => 'Mobile Payment',
                    'description' => 'Use mobile payment options like Google Pay or Apple Pay.',
                    'icon' => 'mobile_payment_icon.png',
                    'icon_alt' => 'Mobile Payment Icon',
                    'country_code' => 'IN',
                    'currency' => 'INR',
                    'link' => 'https://example.com/mobile-payment',
                ],
                // Add more sample payment options as needed
            ];

            foreach ($paymentOptions as $option) {
                DB::table('payment_options')->insert($option);
            }
    }
}
