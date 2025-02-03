<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $place_items = [
            [
                'name' => 'External security camera present',
                'description' => '',
            ],
            [
                'name' => 'Noise decibel monitor present',
                'description' => '',
            ],
            [
                'name' => 'Weapon(s) on the Property',
                'description' => '',
            ],
        ];

        foreach ($place_items as $place_item) {
            DB::table('place_items')->insert($place_item);
        }
    }
}
