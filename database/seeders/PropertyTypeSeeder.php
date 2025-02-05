<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            $propertyTypes = [
                [
                    'name' => 'Apartment',
                    'icon' => 'apartment_icon.png',
                    'description' => 'A self-contained housing unit that occupies only part of a building.',
                ],
                [
                    'name' => 'House',
                    'icon' => 'house_icon.png',
                    'description' => 'A single-family home that typically includes a yard and garage.',
                ],
                [
                    'name' => 'Villa',
                    'icon' => 'villa_icon.png',
                    'description' => 'A luxurious, large house, typically situated in a scenic location.',
                ],
                [
                    'name' => 'Condo',
                    'icon' => 'condo_icon.png',
                    'description' => 'A building or complex containing individually owned units.',
                ],
                [
                    'name' => 'Studio',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Barn',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Bed & Breakfast',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Boat',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Cabin',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Camper/RV',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Container',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Cave',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Dammuso',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Dome',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Farm',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                [
                    'name' => 'Guesthouse',
                    'icon' => 'studio_icon.png',
                    'description' => 'A small apartment consisting of a single room used for both living and sleeping.',
                ],
                // Add more property types as needed
            ];

            foreach ($propertyTypes as $propertyType) {
                DB::table('property_types')->insert($propertyType);
            }

    }
}
