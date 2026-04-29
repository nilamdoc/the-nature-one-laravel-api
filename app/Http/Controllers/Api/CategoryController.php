<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\ProductCategory;
use Throwable;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = ProductCategory::orderBy('display_order', 'asc')->get();

            $data = $categories->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'parent_id' => $item->parent_category ? (string) $item->parent_category : null,
                    'status' => ($item->is_active ?? false) ? 'active' : 'inactive',
                ];
            })->values();

            return ApiResponse::success($data, 'Categories fetched');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch categories', ['error' => $e->getMessage()], 500);
        }
    }
}

