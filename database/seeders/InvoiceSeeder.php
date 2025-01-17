<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $invoices = [
                [
                    'user_id' => 1,
                    'booking_id' => 1,
                    'payment_id' => 1,
                    'amount' => 200.50,
                    'status' => 'paid',
                    'payment_method' => 'credit_card',
                    'due_date' => Carbon::today()->addDays(10)->toDateString(),
                    'description' => 'Invoice for beachfront property booking.',
                    'currency' => 'USD',
                ],
                [
                    'user_id' => 2,
                    'booking_id' => 2,
                    'payment_id' => null,
                    'amount' => 350.75,
                    'status' => 'pending',
                    'payment_method' => 'paypal',
                    'due_date' => Carbon::today()->addDays(15)->toDateString(),
                    'description' => 'Invoice for mountain retreat booking.',
                    'currency' => 'USD',
                ],
                [
                    'user_id' => 3,
                    'booking_id' => 3,
                    'payment_id' => 2,
                    'amount' => 500.00,
                    'status' => 'paid',
                    'payment_method' => 'bank_transfer',
                    'due_date' => Carbon::today()->addDays(20)->toDateString(),
                    'description' => 'Invoice for urban living property booking.',
                    'currency' => 'USD',
                ],
                // Add more sample invoices as needed
            ];

            foreach ($invoices as $invoice) {
                DB::table('invoices')->insert($invoice);
            }
        
    }
}
