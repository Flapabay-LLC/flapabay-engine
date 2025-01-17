<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $paymentMethods = [
                [
                    'user_id' => 1,
                    'type' => 'card',
                    'payment_method' => 'credit_card',
                    'account_number' => '4111111111111111',
                    'expiration_date' => '12/25',
                    'country_code' => 'US',
                    'currency' => 'USD',
                ],
                [
                    'user_id' => 2,
                    'type' => 'paypal',
                    'payment_method' => 'paypal',
                    'account_number' => 'user2@paypal.com',
                    'expiration_date' => null,
                    'country_code' => 'US',
                    'currency' => 'USD',
                ],
                [
                    'user_id' => 3,
                    'type' => 'bank_transfer',
                    'payment_method' => 'bank_transfer',
                    'account_number' => '123456789',
                    'expiration_date' => null,
                    'country_code' => 'UK',
                    'currency' => 'GBP',
                ],
                [
                    'user_id' => 4,
                    'type' => 'mobile',
                    'payment_method' => 'mobile_payment',
                    'account_number' => '9876543210',
                    'expiration_date' => null,
                    'country_code' => 'IN',
                    'currency' => 'INR',
                ],
                // Add more sample payment methods as needed
            ];

            foreach ($paymentMethods as $method) {
                DB::table('payment_methods')->insert($method);
            }
    }
}
