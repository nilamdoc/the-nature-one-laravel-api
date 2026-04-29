<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->delete();

        $mobiles = ProductCategory::where('slug', 'mobiles')->first();
        $laptops = ProductCategory::where('slug', 'laptops')->first();
        $shoes = ProductCategory::where('slug', 'shoes')->first();
        $furniture = ProductCategory::where('slug', 'furniture')->first();

        $rows = [
            [
                'name' => 'Nova X1 Mobile',
                'sku' => 'MOB-NOVA-X1',
                'category' => $mobiles ? (string) $mobiles->id : '',
                'badge' => 'Best Seller',
                'price' => 19999,
                'mrp' => 22999,
                'discount' => 13,
                'stock' => 40,
                'short_description' => 'Fast 5G smartphone.',
                'long_description' => 'Nova X1 with AMOLED display and 5G support.',
                'highlights' => '5G,AMOLED,5000mAh',
                'is_active' => true,
                'is_featured' => true,
                'slug' => 'nova-x1-mobile',
                'image' => '/upload/products/product-1.jpg',
            ],
            [
                'name' => 'WorkPro Laptop 14',
                'sku' => 'LAP-WORKPRO-14',
                'category' => $laptops ? (string) $laptops->id : '',
                'badge' => 'New',
                'price' => 54999,
                'mrp' => 59999,
                'discount' => 8,
                'stock' => 22,
                'short_description' => 'Slim work laptop.',
                'long_description' => '14-inch productivity laptop with SSD storage.',
                'highlights' => 'SSD,Lightweight,Backlit Keyboard',
                'is_active' => true,
                'is_featured' => false,
                'slug' => 'workpro-laptop-14',
                'image' => '/upload/products/product-2.jpg',
            ],
            [
                'name' => 'StreetRun Shoes',
                'sku' => 'SHO-STREETRUN',
                'category' => $shoes ? (string) $shoes->id : '',
                'badge' => '',
                'price' => 2999,
                'mrp' => 3999,
                'discount' => 25,
                'stock' => 70,
                'short_description' => 'Daily running shoes.',
                'long_description' => 'Comfort-focused running shoes for daily wear.',
                'highlights' => 'Breathable,Lightweight,Grip Sole',
                'is_active' => true,
                'is_featured' => false,
                'slug' => 'streetrun-shoes',
                'image' => '/upload/products/product-1.jpg',
            ],
            [
                'name' => 'OakNest Chair',
                'sku' => 'FUR-OAKNEST-CHAIR',
                'category' => $furniture ? (string) $furniture->id : '',
                'badge' => 'Popular',
                'price' => 8999,
                'mrp' => 9999,
                'discount' => 10,
                'stock' => 15,
                'short_description' => 'Ergonomic wooden chair.',
                'long_description' => 'Solid wood chair with ergonomic support.',
                'highlights' => 'Solid Wood,Ergonomic,Durable',
                'is_active' => true,
                'is_featured' => true,
                'slug' => 'oaknest-chair',
                'image' => '/upload/products/product-2.jpg',
            ],
        ];

        foreach ($rows as $row) {
            if ($row['category'] === '') {
                continue;
            }
            Product::create($row);
        }
    }
}

