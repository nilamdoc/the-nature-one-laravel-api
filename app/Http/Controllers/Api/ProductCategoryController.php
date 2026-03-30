<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ApiResponse;

class ProductCategoryController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {
            $categories = ProductCategory::orderBy('display_order', 'asc')->get();

            $data = $categories->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'emoji' => $item->emoji ? asset('storage/' . $item->emoji) : null,
                    'slug' => $item->slug,
                    'display_order' => $item->display_order,
                    'parent_category' => $item->parent_category,
                    'seo_title' => $item->seo_title,
                    'seo_description' => $item->seo_description,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data, 'Categories fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 STORE
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'emoji' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'slug' => 'required|string|max:255|unique:product_categories,slug',
                'display_order' => 'nullable|integer',
                'parent_category' => 'nullable|string|max:255',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean',
            ]);

            if ($request->hasFile('emoji')) {
                $data['emoji'] = $request->file('emoji')->store('categories', 'public');
            }

            $category = ProductCategory::create($data);

            return ApiResponse::success($category, 'Category created successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 SHOW
     */
    public function show($id)
    {
        try {
            $category = ProductCategory::find($id);

            if (!$category) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($category);

        } catch (\Exception $e) {
            return ApiResponse::error('Error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 UPDATE
     */
    public function update(Request $request, $id)
    {
        try {
            $category = ProductCategory::find($id);

            if (!$category) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'emoji' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'slug' => 'required|string|max:255|unique:product_categories,slug,' . $id,
                'display_order' => 'nullable|integer',
                'parent_category' => 'nullable|string|max:255',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean',
            ]);

            if ($request->hasFile('emoji')) {
                if ($category->emoji) {
                    Storage::disk('public')->delete($category->emoji);
                }
                $data['emoji'] = $request->file('emoji')->store('categories', 'public');
            }

            $category->update($data);

            return ApiResponse::success($category, 'Category updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 DELETE
     */
    public function destroy($id)
    {
        try {
            $category = ProductCategory::find($id);

            if (!$category) {
                return ApiResponse::error('Not found', [], 404);
            }

            if ($category->emoji) {
                Storage::disk('public')->delete($category->emoji);
            }

            $category->delete();

            return ApiResponse::success([], 'Category deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }
}