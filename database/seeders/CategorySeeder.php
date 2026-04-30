<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        ProductCategory::query()->get()->each->delete();

        $categoryJson = [
            [
                'name' => 'Plates',
                'slug' => 'plates',
                'subcategories' => ['Round Plates', 'Square Plates'],
            ],
            [
                'name' => 'Bowls',
                'slug' => 'bowls',
                'subcategories' => ['All Bowls'],
            ],
            [
                'name' => 'Bulk Packs',
                'slug' => 'bulk-packs',
                'subcategories' => ['Value Packs'],
            ],
            [
                'name' => 'Combo Sets',
                'slug' => 'combo-sets',
                'subcategories' => ['Party Sets'],
            ],
        ];

        $displayOrder = 1;

        foreach ($categoryJson as $row) {
            $parent = ProductCategory::create([
                'name' => $row['name'],
                'emoji' => null,
                'slug' => $row['slug'],
                'display_order' => $displayOrder++,
                'parent_category' => null,
                'seo_title' => $row['name'],
                'seo_description' => $row['name'] . ' category',
                'is_active' => true,
            ]);

            foreach ($row['subcategories'] as $subName) {
                ProductCategory::create([
                    'name' => $subName,
                    'emoji' => null,
                    'slug' => \Illuminate\Support\Str::slug($subName),
                    'display_order' => $displayOrder++,
                    'parent_category' => (string) $parent->id,
                    'seo_title' => $subName,
                    'seo_description' => $subName . ' category',
                    'is_active' => true,
                ]);
            }
        }
    }
}
