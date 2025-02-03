<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $amenities = [
            [
                'name' => 'Hot tub',
                'white_icon' => 'bath',
                'black_icon' => 'bath',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Pool',
                'white_icon' => 'swimming_prool',
                'black_icon' => 'swimming_prool',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Patio',
                'white_icon' => 'patio',
                'black_icon' => 'patio',
                'svg' => '',
                'description' => '',
            ],
        ];

        foreach ($amenities as $amenity) {
            DB::table('amenities')->insert($amenity);
        }
    }
}
