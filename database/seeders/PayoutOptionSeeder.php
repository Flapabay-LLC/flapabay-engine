<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayoutOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            $payoutOptions = [
                [
                    'name' => 'Bank Transfer',
                    'description' => 'Receive your payouts directly to your bank account.',
                    'icon' => 'bank_transfer_icon.png',
                    'icon_alt' => 'Bank Transfer Icon',
                    'country_code' => 'US',
                    'currency' => 'USD',
                    'link' => 'https://example.com/bank-transfer',
                ],
                [
                    'name' => 'PayPal',
                    'description' => 'Get your payouts instantly through PayPal.',
                    'icon' => 'paypal_icon.png',
                    'icon_alt' => 'PayPal Icon',
                    'country_code' => 'US',
                    'currency' => 'USD',
                    'link' => 'https://example.com/paypal',
                ],
                [
                    'name' => 'Stripe',
                    'description' => 'Receive payouts via Stripe.',
                    'icon' => 'stripe_icon.png',
                    'icon_alt' => 'Stripe Icon',
                    'country_code' => 'UK',
                    'currency' => 'GBP',
                    'link' => 'https://example.com/stripe',
                ],
                [
                    'name' => 'Mobile Payment',
                    'description' => 'Get paid through mobile payment options like Google Pay or Apple Pay.',
                    'icon' => 'mobile_payment_icon.png',
                    'icon_alt' => 'Mobile Payment Icon',
                    'country_code' => 'IN',
                    'currency' => 'INR',
                    'link' => 'https://example.com/mobile-payment',
                ],
                // Add more sample payout options as needed
            ];

            foreach ($payoutOptions as $option) {
                DB::table('payout_options')->insert($option);
            }
            
    }
}
