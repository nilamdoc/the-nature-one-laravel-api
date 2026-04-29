<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        ProductCategory::query()->delete();

        $electronics = ProductCategory::create([
            'name' => 'Electronics',
            'emoji' => null,
            'slug' => 'electronics',
            'display_order' => 1,
            'parent_category' => null,
            'seo_title' => 'Electronics',
            'seo_description' => 'Electronics category',
            'is_active' => true,
        ]);

        $fashion = ProductCategory::create([
            'name' => 'Fashion',
            'emoji' => null,
            'slug' => 'fashion',
            'display_order' => 2,
            'parent_category' => null,
            'seo_title' => 'Fashion',
            'seo_description' => 'Fashion category',
            'is_active' => true,
        ]);

        $homeLiving = ProductCategory::create([
            'name' => 'Home & Living',
            'emoji' => null,
            'slug' => 'home-living',
            'display_order' => 3,
            'parent_category' => null,
            'seo_title' => 'Home & Living',
            'seo_description' => 'Home and living category',
            'is_active' => true,
        ]);

        ProductCategory::insert([
            [
                'name' => 'Mobiles',
                'emoji' => null,
                'slug' => 'mobiles',
                'display_order' => 4,
                'parent_category' => (string) $electronics->id,
                'seo_title' => 'Mobiles',
                'seo_description' => 'Mobile phones',
                'is_active' => true,
            ],
            [
                'name' => 'Laptops',
                'emoji' => null,
                'slug' => 'laptops',
                'display_order' => 5,
                'parent_category' => (string) $electronics->id,
                'seo_title' => 'Laptops',
                'seo_description' => 'Laptop products',
                'is_active' => true,
            ],
            [
                'name' => 'Shoes',
                'emoji' => null,
                'slug' => 'shoes',
                'display_order' => 6,
                'parent_category' => (string) $fashion->id,
                'seo_title' => 'Shoes',
                'seo_description' => 'Footwear collection',
                'is_active' => true,
            ],
            [
                'name' => 'Furniture',
                'emoji' => null,
                'slug' => 'furniture',
                'display_order' => 7,
                'parent_category' => (string) $homeLiving->id,
                'seo_title' => 'Furniture',
                'seo_description' => 'Furniture products',
                'is_active' => true,
            ],
        ]);
    }
}

