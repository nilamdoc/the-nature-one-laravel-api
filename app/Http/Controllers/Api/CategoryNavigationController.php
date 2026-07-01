<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Product;
use App\Models\ProductCategory;
use Throwable;

class CategoryNavigationController extends Controller
{
    public function mainCategories()
    {
        try {
            $rows = ProductCategory::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('parent_category')->orWhere('parent_category', '');
                })
                ->orderBy('display_order', 'asc')
                ->get()
                ->map(function ($item) {
                    $name = (string) ($item->name ?? '');
                    return [
                        'id' => (string) $item->id,
                        'name' => $name,
                        'slug' => (string) ($item->slug ?? ''),
                        'path' => '/shop?category=' . urlencode($name),
                    ];
                })
                ->values()
                ->all();

            return ApiResponse::success($rows, 'Main categories fetched');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch main categories', ['error' => $e->getMessage()], 500);
        }
    }

    public function subCategories(string $mainId)
    {
        try {
            $children = ProductCategory::where('is_active', true)
                ->where('parent_category', $mainId)
                ->orderBy('display_order', 'asc')
                ->get();

            $categoryIds = $children->pluck('id')->map(fn ($id) => (string) $id)->all();

            if (count($categoryIds) === 0) {
                $parent = ProductCategory::where('is_active', true)->find($mainId);
                if (!$parent) {
                    return ApiResponse::notFound('Main category not found');
                }
                $children = collect([$parent]);
                $categoryIds = [(string) $parent->id];
            }

            $products = Product::where('is_active', true)
                ->whereIn('category', $categoryIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $productsByCategory = [];
            foreach ($products as $product) {
                $categoryId = (string) ($product->category ?? '');
                if (!$categoryId) continue;
                $productsByCategory[$categoryId][] = [
                    'name' => (string) ($product->name ?? ''),
                    'path' => '/product/' . (string) $product->id,
                    'image' => (string) ($product->image ?? '/placeholder.svg'),
                    'price' => (float) ($product->price ?? 0),
                    'originalPrice' => (float) ($product->mrp ?? 0),
                ];
            }

            $data = $children->map(function ($item) use ($productsByCategory) {
                $id = (string) $item->id;
                $name = (string) ($item->name ?? '');
                return [
                    'id' => $id,
                    'name' => $name,
                    'path' => '/shop?category=' . urlencode($name),
                    'products' => array_slice($productsByCategory[$id] ?? [], 0, 6),
                ];
            })->values()->all();

            return ApiResponse::success($data, 'Subcategories fetched');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch subcategories', ['error' => $e->getMessage()], 500);
        }
    }
}

