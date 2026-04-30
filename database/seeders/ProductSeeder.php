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
        Product::query()->get()->each->delete();

        $productJson = [
            ['name' => '10" Round Dinner Plates', 'category' => 'Plates', 'price' => 499, 'mrp' => 799, 'badge' => 'Best Seller', 'image' => '/upload/products/product-1.jpg'],
            ['name' => '8" Salad Plates', 'category' => 'Plates', 'price' => 399, 'mrp' => 649, 'badge' => '', 'image' => '/upload/products/product-2.jpg'],
            ['name' => '4" Dessert Plates', 'category' => 'Plates', 'price' => 299, 'mrp' => 499, 'badge' => '', 'image' => '/upload/products/product-3.jpg'],
            ['name' => 'Square Plates - 25 Pack', 'category' => 'Plates', 'price' => 599, 'mrp' => 899, 'badge' => '', 'image' => '/upload/products/product-4.jpg'],
            ['name' => 'Deep Round Bowls', 'category' => 'Bowls', 'price' => 449, 'mrp' => 749, 'badge' => '', 'image' => '/upload/products/product-3.jpg'],
            ['name' => 'Dessert Bowls', 'category' => 'Bowls', 'price' => 299, 'mrp' => 499, 'badge' => '', 'image' => '/upload/products/product-3.jpg'],
            ['name' => 'Bulk 200 Pack', 'category' => 'Bulk Packs', 'price' => 3999, 'mrp' => 4999, 'badge' => 'Popular', 'image' => '/upload/products/product-4.jpg'],
            ['name' => 'Mega 500 Pack', 'category' => 'Bulk Packs', 'price' => 8999, 'mrp' => 10999, 'badge' => 'Best Value', 'image' => '/upload/products/product-4.jpg'],
            ['name' => 'Party Combo Set', 'category' => 'Combo Sets', 'price' => 899, 'mrp' => 1299, 'badge' => 'New', 'image' => '/upload/products/product-5.jpg'],
        ];

        foreach ($productJson as $index => $row) {
            $category = ProductCategory::where('name', $row['category'])
                ->whereNull('parent_category')
                ->first();

            if (!$category) {
                continue;
            }

            $name = $row['name'];
            $slug = Str::slug($name);
            $discount = max(0, (int) round((($row['mrp'] - $row['price']) / $row['mrp']) * 100));

            Product::create([
                'name' => $name,
                'slug' => $slug,
                'sku' => strtoupper(substr(Str::slug($row['category']), 0, 3)) . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'category' => (string) $category->id,
                'badge' => $row['badge'],
                'price' => $row['price'],
                'mrp' => $row['mrp'],
                'discount' => $discount,
                'stock' => random_int(15, 200),
                'short_description' => $name,
                'long_description' => $name . ' from The Nature One catalog.',
                'highlights' => 'Food Safe,Leak Resistant,Eco Friendly',
                'image' => $row['image'],
                'is_active' => true,
                'is_featured' => in_array($row['badge'], ['Best Seller', 'Popular', 'Best Value'], true),
            ]);
        }
    }
}
