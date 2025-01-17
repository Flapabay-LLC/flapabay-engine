<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            $payments = [
                [
                    'booking_id' => 1,
                    'payment_method' => 'credit_card',
                    'amount' => 200.50,
                    'status' => 'completed',
                ],
                [
                    'booking_id' => 2,
                    'payment_method' => 'paypal',
                    'amount' => 350.75,
                    'status' => 'pending',
                ],
                [
                    'booking_id' => 3,
                    'payment_method' => 'bank_transfer',
                    'amount' => 500.00,
                    'status' => 'completed',
                ],
                [
                    'booking_id' => 4,
                    'payment_method' => 'mobile_payment',
                    'amount' => 150.25,
                    'status' => 'failed',
                ],
                // Add more sample payments as needed
            ];

            foreach ($payments as $payment) {
                DB::table('payments')->insert($payment);
            }
            
    }
}
