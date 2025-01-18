<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            $categories = [
                [
                    'name' => 'Beachfront',
                    'icon' => 'beachfront-icon.png',
                    'icon_alt' => 'Beachfront Icon',
                    'description' => 'Properties located on the beachfront.',
                ],
                [
                    'name' => 'Mountain Retreat',
                    'icon' => 'mountain-icon.png',
                    'icon_alt' => 'Mountain Retreat Icon',
                    'description' => 'Properties nestled in the mountains.',
                ],
                [
                    'name' => 'Urban Living',
                    'icon' => 'urban-icon.png',
                    'icon_alt' => 'Urban Living Icon',
                    'description' => 'Properties located in the heart of the city.',
                ],
                [
                    'name' => 'Countryside',
                    'icon' => 'countryside-icon.png',
                    'icon_alt' => 'Countryside Icon',
                    'description' => 'Properties in peaceful countryside areas.',
                ],
                [
                    'name' => 'Luxury Villas',
                    'icon' => 'luxury-villa-icon.png',
                    'icon_alt' => 'Luxury Villas Icon',
                    'description' => 'High-end villas for luxurious living.',
                ],
            ];

            foreach ($categories as $category) {
                DB::table('categories')->insert($category);
            }
    }
}
