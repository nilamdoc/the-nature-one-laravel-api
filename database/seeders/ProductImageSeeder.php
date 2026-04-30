<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        ProductImage::query()->delete();

        $products = Product::all();

        foreach ($products as $product) {
            $baseImage = (string) ($product->image ?? '/upload/products/product-1.jpg');
            $variantA = preg_replace('/(\.\w+)$/', '-alt-1$1', $baseImage) ?? $baseImage;
            $variantB = preg_replace('/(\.\w+)$/', '-alt-2$1', $baseImage) ?? $baseImage;

            ProductImage::create([
                'product_id' => (string) $product->id,
                'image_url' => $baseImage,
                'is_primary' => true,
            ]);

            ProductImage::create([
                'product_id' => (string) $product->id,
                'image_url' => $variantA,
                'is_primary' => false,
            ]);

            ProductImage::create([
                'product_id' => (string) $product->id,
                'image_url' => $variantB,
                'is_primary' => false,
            ]);
        }
    }
}

