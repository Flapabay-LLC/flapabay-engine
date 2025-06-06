<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $favorites = [
            [
                'name' => 'Wifi',
                'white_icon' => 'wifi',
                'black_icon' => 'wifi',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'TV',
                'white_icon' => 'tv',
                'black_icon' => 'tv',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Kitchen',
                'white_icon' => 'kitchen',
                'black_icon' => 'kitchen',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Washer',
                'white_icon' => 'washer',
                'black_icon' => 'washer',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Free parking on premises',
                'white_icon' => 'washer',
                'black_icon' => 'washer',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Pad parking on premises',
                'white_icon' => 'washer',
                'black_icon' => 'washer',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Air conditioning',
                'white_icon' => 'washer',
                'black_icon' => 'washer',
                'svg' => '',
                'description' => '',
            ],
            [
                'name' => 'Dedicated workspace',
                'white_icon' => 'washer',
                'black_icon' => 'washer',
                'svg' => '',
                'description' => '',
            ],
        ];

        foreach ($favorites as $favorite) {
            DB::table('favorites')->insert($favorite);
        }
    }
}
