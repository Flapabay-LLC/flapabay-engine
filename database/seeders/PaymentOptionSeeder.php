<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            DB::table('payment_options')->insert([
                [
                    'user_id' => 1,
                    'payment_method' => 'credit_card',
                    'account_number' => '4111111111111111',
                    'expiration_date' => '12/25',
                    'country_code' => 'US',
                    'currency' => 'USD',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'user_id' => 2,
                    'payment_method' => 'paypal',
                    'account_number' => 'user@example.com',
                    'expiration_date' => null,
                    'country_code' => 'US',
                    'currency' => 'USD',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'user_id' => 1,
                    'payment_method' => 'bank_transfer',
                    'account_number' => '123456789',
                    'expiration_date' => null,
                    'country_code' => 'IN',
                    'currency' => 'INR',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
    }
}
