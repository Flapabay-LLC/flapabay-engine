<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PropertyType;

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
                'black_icon' => 'Building',
                'white_icon' => 'Building',
                'description' => 'A self-contained housing unit in a building',
                'bg_color' => '#E3F2FD',
                'color' => '#1976D2',
                'type' => 'stay'
            ],
            [
                'name' => 'House',
                'black_icon' => 'House',
                'white_icon' => 'House',
                'description' => 'A single-family residential building',
                'bg_color' => '#E8F5E9',
                'color' => '#2E7D32',
                'type' => 'experience'
            ],
            [
                'name' => 'Villa',
                'black_icon' => 'House2',
                'white_icon' => 'House2',
                'description' => 'A large, luxurious house, often in a resort or vacation area',
                'bg_color' => '#FFF3E0',
                'color' => '#E65100',
                'type' => 'experience'
            ],
            [
                'name' => 'Condo',
                'black_icon' => 'Building2',
                'white_icon' => 'Building2',
                'description' => 'A privately owned individual unit within a building',
                'bg_color' => '#E8EAF6',
                'color' => '#3949AB',
                'type' => 'experience'
            ],
            [
                'name' => 'Cottage',
                'black_icon' => 'House3',
                'white_icon' => 'House3',
                'description' => 'A small house, typically one in the country',
                'bg_color' => '#F3E5F5',
                'color' => '#7B1FA2',
                'type' => 'experience'
            ],
            [
                'name' => 'Cabin',
                'black_icon' => 'House4',
                'white_icon' => 'House4',
                'description' => 'A small wooden shelter or house in a wild or remote area',
                'bg_color' => '#E0F2F1',
                'color' => '#00695C',
                'type' => 'stay'
            ],
            [
                'name' => 'Bungalow',
                'black_icon' => 'House5',
                'white_icon' => 'House5',
                'description' => 'A low house with a broad front porch',
                'bg_color' => '#FBE9E7',
                'color' => '#D84315',
                'type' => 'stay'
            ],
            [
                'name' => 'Loft',
                'black_icon' => 'Building3',
                'white_icon' => 'Building3',
                'description' => 'A large, open space converted from industrial use',
                'bg_color' => '#ECEFF1',
                'color' => '#455A64',
                'type' => 'stay'
            ],
            [
                'name' => 'Studio',
                'black_icon' => 'Building4',
                'white_icon' => 'Building4',
                'description' => 'A small apartment with a combined living and sleeping area',
                'bg_color' => '#E0F7FA',
                'color' => '#00838F',
                'type' => 'stay'
            ],
            [
                'name' => 'Townhouse',
                'black_icon' => 'Building5',
                'white_icon' => 'Building5',
                'description' => 'A house that shares one or more walls with adjacent properties',
                'bg_color' => '#F1F8E9',
                'color' => '#558B2F',
                'type' => 'experience'
            ]
        ];

        foreach ($propertyTypes as $type) {
            PropertyType::create($type);
        }

        $this->command->info('Property types seeded successfully!');
    }
}
