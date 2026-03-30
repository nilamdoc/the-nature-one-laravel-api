<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class ProductController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {
            $products = Product::orderBy('created_at', 'desc')->get();

            $data = $products->map(function ($item) {
                $category = ProductCategory::find($item->category);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'category' => $category ? ['id' => $category->id, 'name' => $category->name] : null,
                    'badge' => $item->badge,
                    'price' => $item->price,
                    'mrp' => $item->mrp,
                    'discount' => $item->discount,
                    'stock' => $item->stock,
                    'short_description' => $item->short_description,
                    'long_description' => $item->long_description,
                    'highlights' => $item->highlights ? explode(',', $item->highlights) : [],
                    'is_active' => $item->is_active,
                    'is_featured' => $item->is_featured,
                ];
            });

            return ApiResponse::success($data, 'Products fetched');

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
                'sku' => 'required|string|max:100|unique:products,sku',
                'category' => 'required|string', // category id
                'badge' => 'nullable|string|max:255',
                'price' => 'required|numeric',
                'mrp' => 'nullable|numeric',
                'discount' => 'nullable|numeric',
                'stock' => 'required|integer',
                'short_description' => 'nullable|string|max:500',
                'long_description' => 'nullable|string',
                'highlights' => 'nullable|string', // comma-separated
                'is_active' => 'required|boolean',
                'is_featured' => 'required|boolean',
            ]);

            $product = Product::create($data);

            return ApiResponse::success($product, 'Product created successfully');

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
            $item = Product::find($id);

            if (!$item) {
                return ApiResponse::error('Not found', [], 404);
            }

            $category = ProductCategory::find($item->category);

            $data = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'category' => $category ? ['id' => $category->id, 'name' => $category->name] : null,
                'badge' => $item->badge,
                'price' => $item->price,
                'mrp' => $item->mrp,
                'discount' => $item->discount,
                'stock' => $item->stock,
                'short_description' => $item->short_description,
                'long_description' => $item->long_description,
                'highlights' => $item->highlights ? explode(',', $item->highlights) : [],
                'is_active' => $item->is_active,
                'is_featured' => $item->is_featured,
            ];

            return ApiResponse::success($data);

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
            $product = Product::find($id);

            if (!$product) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100|unique:products,sku,' . $id,
                'category' => 'required|string',
                'badge' => 'nullable|string|max:255',
                'price' => 'required|numeric',
                'mrp' => 'nullable|numeric',
                'discount' => 'nullable|numeric',
                'stock' => 'required|integer',
                'short_description' => 'nullable|string|max:500',
                'long_description' => 'nullable|string',
                'highlights' => 'nullable|string',
                'is_active' => 'required|boolean',
                'is_featured' => 'required|boolean',
            ]);

            $product->update($data);

            return ApiResponse::success($product, 'Product updated successfully');

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
            $product = Product::find($id);

            if (!$product) {
                return ApiResponse::error('Not found', [], 404);
            }

            $product->delete();

            return ApiResponse::success([], 'Product deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }
}