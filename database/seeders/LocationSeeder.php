<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // Lusaka Province
            [
                'name' => 'Lusaka',
                'code' => 'LSK',
                'description' => 'Capital city of Zambia, known for its bustling markets and vibrant culture.',
                'placeholder_img' => 'https://example.com/images/lusaka-placeholder.jpg',
            ],
            [
                'name' => 'Kafue',
                'code' => 'KAF',
                'description' => 'Known for its industries and Kafue National Park.',
                'placeholder_img' => 'https://example.com/images/kafue-placeholder.jpg',
            ],
            [
                'name' => 'Chongwe',
                'code' => 'CHO',
                'description' => 'A district close to Lusaka, with agricultural significance.',
                'placeholder_img' => 'https://example.com/images/chongwe-placeholder.jpg',
            ],
            // Copperbelt Province
            [
                'name' => 'Kitwe',
                'code' => 'KIT',
                'description' => 'A major city in the Copperbelt Province, known for its mining industry.',
                'placeholder_img' => 'https://example.com/images/kitwe-placeholder.jpg',
            ],
            [
                'name' => 'Ndola',
                'code' => 'NDL',
                'description' => 'Commercial and industrial center in the Copperbelt Province.',
                'placeholder_img' => 'https://example.com/images/ndola-placeholder.jpg',
            ],
            [
                'name' => 'Chingola',
                'code' => 'CHN',
                'description' => 'Known for its copper mines, part of the Copperbelt.',
                'placeholder_img' => 'https://example.com/images/chingola-placeholder.jpg',
            ],
            [
                'name' => 'Mufulira',
                'code' => 'MFU',
                'description' => 'Another mining town in the Copperbelt Province.',
                'placeholder_img' => 'https://example.com/images/mufulira-placeholder.jpg',
            ],
            // Southern Province
            [
                'name' => 'Livingstone',
                'code' => 'LIV',
                'description' => 'Tourist city near Victoria Falls, known for adventure activities.',
                'placeholder_img' => 'https://example.com/images/livingstone-placeholder.jpg',
            ],
            [
                'name' => 'Choma',
                'code' => 'CHO',
                'description' => 'Capital of Southern Province, an agricultural hub.',
                'placeholder_img' => 'https://example.com/images/choma-placeholder.jpg',
            ],
            [
                'name' => 'Mazabuka',
                'code' => 'MAZ',
                'description' => 'Known for sugar production, home to Zambia Sugar Company.',
                'placeholder_img' => 'https://example.com/images/mazabuka-placeholder.jpg',
            ],
            [
                'name' => 'Kalomo',
                'code' => 'KLM',
                'description' => 'Historical town, close to the Kalomo Hills.',
                'placeholder_img' => 'https://example.com/images/kalomo-placeholder.jpg',
            ],
            // Eastern Province
            [
                'name' => 'Chipata',
                'code' => 'CPT',
                'description' => 'Eastern Province city, a gateway to South Luangwa National Park.',
                'placeholder_img' => 'https://example.com/images/chipata-placeholder.jpg',
            ],
            [
                'name' => 'Petauke',
                'code' => 'PET',
                'description' => 'Known for agriculture, especially maize and cotton farming.',
                'placeholder_img' => 'https://example.com/images/petauke-placeholder.jpg',
            ],
            [
                'name' => 'Katete',
                'code' => 'KAT',
                'description' => 'Small town in the Eastern Province, known for cultural heritage.',
                'placeholder_img' => 'https://example.com/images/katete-placeholder.jpg',
            ],
            [
                'name' => 'Lundazi',
                'code' => 'LUN',
                'description' => 'Close to the Luangwa River, known for wildlife and nature.',
                'placeholder_img' => 'https://example.com/images/lundazi-placeholder.jpg',
            ],
            // Northern Province
            [
                'name' => 'Kasama',
                'code' => 'KAS',
                'description' => 'Capital of Northern Province, known for Chishimba Falls.',
                'placeholder_img' => 'https://example.com/images/kasama-placeholder.jpg',
            ],
            [
                'name' => 'Mbala',
                'code' => 'MBA',
                'description' => 'Northernmost town, near Lake Tanganyika.',
                'placeholder_img' => 'https://example.com/images/mbala-placeholder.jpg',
            ],
            [
                'name' => 'Mpulungu',
                'code' => 'MPU',
                'description' => 'Zambia’s only port town, located on Lake Tanganyika.',
                'placeholder_img' => 'https://example.com/images/mpulungu-placeholder.jpg',
            ],
            [
                'name' => 'Luwingu',
                'code' => 'LUW',
                'description' => 'A rural town in Northern Province with beautiful landscapes.',
                'placeholder_img' => 'https://example.com/images/luwingu-placeholder.jpg',
            ],
            // Add more districts from other provinces as needed
        ];

        DB::table('locations')->insert($locations);
    }
}
