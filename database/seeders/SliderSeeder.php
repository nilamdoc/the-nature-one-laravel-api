<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('sliders')->insert([
            [
                'title' => 'Choose Safe Living For Your Family',
                'subtitle' => 'Premium eco-friendly tableware',
                'image' => '/upload/sliders/slider-1.jpg',
                'link' => '/shop',
                'button_text' => 'Shop All',
                'order' => 1,
                'sort_order' => 1,
                'status' => 'active',
                'status_bool' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'From Nature To Nature',
                'subtitle' => 'No microplastics. No toxins.',
                'image' => '/upload/sliders/slider-2.jpg',
                'link' => '/about',
                'button_text' => 'Explore',
                'order' => 2,
                'sort_order' => 2,
                'status' => 'active',
                'status_bool' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Compostable Dining For Every Occasion',
                'subtitle' => 'Elegant tableware for events and everyday meals',
                'image' => '/upload/sliders/slider-3.jpg',
                'link' => '/shop?category=Plates',
                'button_text' => 'Browse Plates',
                'order' => 3,
                'sort_order' => 3,
                'status' => 'active',
                'status_bool' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Bulk Packs For Restaurants & Caterers',
                'subtitle' => 'Reliable inventory with premium food-safe quality',
                'image' => '/upload/sliders/slider-4.jpg',
                'link' => '/contact',
                'button_text' => 'Get Bulk Quote',
                'order' => 4,
                'sort_order' => 4,
                'status' => 'active',
                'status_bool' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
