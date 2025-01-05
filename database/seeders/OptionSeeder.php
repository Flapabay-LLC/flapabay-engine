<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            [
                'country_code' => 'US',
                'currency' => 'USD',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'country_code' => 'IN',
                'currency' => 'INR',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'country_code' => 'GB',
                'currency' => 'GBP',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'country_code' => 'EU',
                'currency' => 'EUR',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'country_code' => 'ZM',
                'currency' => 'ZMW',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('options')->insert($options);
    }
}
