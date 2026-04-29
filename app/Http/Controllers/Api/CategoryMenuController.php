<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Product;
use App\Models\ProductCategory;
use Throwable;

class CategoryMenuController extends Controller
{
    public function index()
    {
        try {
            $categories = ProductCategory::where('is_active', true)
                ->orderBy('display_order', 'asc')
                ->get();

            $products = Product::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            $categoryMap = $categories->keyBy(fn ($category) => (string) $category->id);

            $productsByCategory = [];
            foreach ($products as $product) {
                $categoryId = (string) ($product->category ?? '');
                if (!$categoryId) {
                    continue;
                }

                $productsByCategory[$categoryId][] = [
                    'name' => (string) $product->name,
                    'path' => '/product/' . (string) $product->id,
                    'image' => (string) ($product->image ?? '/upload/products/product-1.jpg'),
                    'price' => (float) ($product->price ?? 0),
                    'originalPrice' => (float) ($product->mrp ?? 0),
                ];
            }

            $parents = $categories->filter(fn ($item) => empty($item->parent_category))->values();

            $menu = $parents->map(function ($parent) use ($categories, $productsByCategory, $categoryMap) {
                $parentId = (string) $parent->id;
                $children = $categories
                    ->filter(fn ($item) => (string) ($item->parent_category ?? '') === $parentId)
                    ->values();

                $megaCategories = $children->map(function ($child) use ($productsByCategory) {
                    $childId = (string) $child->id;
                    return [
                        'id' => $childId,
                        'name' => (string) $child->name,
                        'path' => '/shop?category=' . urlencode((string) $child->name),
                        'products' => array_slice($productsByCategory[$childId] ?? [], 0, 6),
                    ];
                })->all();

                if (count($megaCategories) === 0) {
                    $megaCategories[] = [
                        'name' => (string) $parent->name,
                        'path' => '/shop?category=' . urlencode((string) $parent->name),
                        'products' => array_slice($productsByCategory[$parentId] ?? [], 0, 6),
                    ];
                }

                return [
                    'id' => $parentId,
                    'name' => (string) $parent->name,
                    'path' => '/shop?category=' . urlencode((string) $parent->name),
                    'hasMega' => true,
                    'megaCategories' => $megaCategories,
                    'children' => array_map(fn ($child) => ['id' => $child['id'] ?? '', 'name' => $child['name'] ?? ''], $megaCategories),
                ];
            })->values()->all();

            array_unshift($menu, [
                'name' => 'Shop All',
                'path' => '/shop',
                'hasMega' => true,
                'megaCategories' => array_values(array_filter(array_map(function ($parent) use ($menu) {
                    return [
                        'name' => $parent['name'],
                        'path' => $parent['path'],
                        'products' => isset($parent['megaCategories'][0]['products']) ? $parent['megaCategories'][0]['products'] : [],
                    ];
                }, $menu), fn ($entry) => !empty($entry['products']))),
            ]);

            $menu[] = [
                'name' => 'About Us',
                'path' => '/about',
                'hasSubmenu' => true,
                'subMenuItems' => [
                    ['name' => 'Founder Story', 'path' => '/about/founder-story'],
                    ['name' => 'Mission & Vision', 'path' => '/about/mission-vision'],
                    ['name' => 'Journey Of TheNatureOne', 'path' => '/about/journey'],
                ],
            ];

            $menu[] = [
                'name' => 'Blogs',
                'path' => '/blog',
            ];

            return ApiResponse::success($menu, 'Menu fetched');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch menu', ['error' => $e->getMessage()], 500);
        }
    }
}
