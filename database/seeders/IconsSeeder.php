<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IconsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $icons = [
            [
                'black_icon' => 'home',
                'white_icon' => 'home-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/home.png',
            ],
            [
                'black_icon' => 'account',
                'white_icon' => 'account-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/account.png',
            ],
            [
                'black_icon' => 'settings',
                'white_icon' => 'settings-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/settings.png',
            ],
            // Add more icons as needed
            [
                'black_icon' => 'email',
                'white_icon' => 'email-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/email.png',
            ],
            [
                'black_icon' => 'camera',
                'white_icon' => 'camera-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/camera.png',
            ],
            [
                'black_icon' => 'bell',
                'white_icon' => 'bell-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/bell.png',
            ],
            [
                'black_icon' => 'cart',
                'white_icon' => 'cart-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/cart.png',
            ],
            [
                'black_icon' => 'chat',
                'white_icon' => 'chat-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/chat.png',
            ],
            [
                'black_icon' => 'map',
                'white_icon' => 'map-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/map.png',
            ],

            // Property Type Icons
            [
                'black_icon' => 'apartment',
                'white_icon' => 'apartment-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/apartment.png',
            ],
            [
                'black_icon' => 'house',
                'white_icon' => 'house-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/house.png',
            ],
            [
                'black_icon' => 'villa',
                'white_icon' => 'villa-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/villa.png',
            ],

            // Category Icons
            [
                'black_icon' => 'commercial',
                'white_icon' => 'commercial-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/commercial.png',
            ],
            [
                'black_icon' => 'land',
                'white_icon' => 'land-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/land.png',
            ],
            [
                'black_icon' => 'office',
                'white_icon' => 'office-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/office.png',
            ],

            // More property-related icons
            [
                'black_icon' => 'pool',
                'white_icon' => 'pool-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/pool.png',
            ],
            [
                'black_icon' => 'garage',
                'white_icon' => 'garage-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/garage.png',
            ],
            [
                'black_icon' => 'garden',
                'white_icon' => 'garden-outline',
                'svg' => '<svg>...</svg>',
                'icon_image_url' => 'https://example.com/icons/garden.png',
            ],
        ];

        DB::table('icons')->insert($icons);
    }
}
