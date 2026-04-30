<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::orderBy('created_at', 'desc')->get();
            $categoryIds = $products->pluck('category')->filter()->unique()->values();
            $categoryMap = ProductCategory::whereIn('_id', $categoryIds)->get()->keyBy(fn ($item) => (string) $item->id);
            $productIds = $products->pluck('id')->map(fn ($id) => (string) $id)->values();
            $imageMap = ProductImage::whereIn('product_id', $productIds)
                ->orderBy('is_primary', 'desc')
                ->get()
                ->groupBy(fn ($img) => (string) $img->product_id);

            $data = $products->map(function ($item) use ($categoryMap, $imageMap) {
                return $this->transformProduct(
                    $item,
                    $categoryMap[(string) $item->category] ?? null,
                    $imageMap[(string) $item->id] ?? collect()
                );
            })->values();

            return ApiResponse::success($data, 'Products fetched');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'sku' => 'required|string|max:100',
                'category' => 'required|string',
                'badge' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'stock' => 'required|integer|min:0',
                'short_description' => 'nullable|string|max:500',
                'long_description' => 'nullable|string',
                'highlights' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
                'is_active' => 'required|boolean',
                'is_featured' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return ApiResponse::validation($validator);
            }

            $data = $validator->validated();

            if (!$this->categoryExists((string) $data['category'])) {
                return ApiResponse::error('Validation failed', ['category' => ['Selected category does not exist.']], 422);
            }

            if (Product::where('sku', (string) $data['sku'])->exists()) {
                return ApiResponse::error('Validation failed', ['sku' => ['The SKU has already been taken.']], 422);
            }

            $data['slug'] = $this->buildUniqueSlug((string) ($data['slug'] ?? $data['name']));
            $data['highlights'] = $this->normalizeHighlights($data['highlights'] ?? null);

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request, 'image');
            } else {
                $data['image'] = null;
            }

            $product = Product::create($data);
            $category = ProductCategory::find((string) $product->category);

            return ApiResponse::success($this->transformProduct($product, $category), 'Product created successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $item = Product::find($id);
            if (!$item) {
                $item = Product::where('slug', $id)->first();
            }

            if (!$item) {
                return ApiResponse::error('Not found', [], 404);
            }

            $category = ProductCategory::find($item->category);
            $images = ProductImage::where('product_id', (string) $item->id)
                ->orderBy('is_primary', 'desc')
                ->get();

            return ApiResponse::success($this->transformProduct($item, $category, $images));
        } catch (Throwable $e) {
            return ApiResponse::error('Error', ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return ApiResponse::error('Not found', [], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'sku' => 'required|string|max:100',
                'category' => 'required|string',
                'badge' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'stock' => 'required|integer|min:0',
                'short_description' => 'nullable|string|max:500',
                'long_description' => 'nullable|string',
                'highlights' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
                'is_active' => 'required|boolean',
                'is_featured' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return ApiResponse::validation($validator);
            }

            $data = $validator->validated();

            if (!$this->categoryExists((string) $data['category'])) {
                return ApiResponse::error('Validation failed', ['category' => ['Selected category does not exist.']], 422);
            }

            if (Product::where('sku', (string) $data['sku'])->where('_id', '!=', (string) $product->id)->exists()) {
                return ApiResponse::error('Validation failed', ['sku' => ['The SKU has already been taken.']], 422);
            }

            $data['slug'] = $this->buildUniqueSlug((string) ($data['slug'] ?? $data['name']), (string) $product->id);
            $data['highlights'] = $this->normalizeHighlights($data['highlights'] ?? null);

            if ($request->hasFile('image')) {
                if (!empty($product->image)) {
                    $this->deleteImageIfExists((string) $product->image);
                }

                $data['image'] = $this->uploadImage($request, 'image');
            } else {
                $data['image'] = (string) ($product->image ?? '');
            }

            $product->update($data);
            $category = ProductCategory::find((string) $product->category);

            return ApiResponse::success($this->transformProduct($product->fresh() ?? $product, $category), 'Product updated successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return ApiResponse::error('Not found', [], 404);
            }

            if (!empty($product->image)) {
                $this->deleteImageIfExists((string) $product->image);
            }

            $product->delete();

            return ApiResponse::success([], 'Product deleted successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }

    private function transformProduct(Product $item, ?ProductCategory $category = null, $images = null): array
    {
        $images = $images ?? collect();
        $gallery = $images->map(fn ($img) => [
            'id' => (string) ($img->id ?? $img->_id),
            'image_url' => (string) ($img->image_url ?? ''),
            'is_primary' => (bool) ($img->is_primary ?? false),
        ])->values()->all();

        $primaryImage = collect($gallery)->firstWhere('is_primary', true);

        return [
            'id' => (string) ($item->id ?? $item->_id),
            'name' => (string) $item->name,
            'slug' => (string) ($item->slug ?? ''),
            'sku' => (string) $item->sku,
            'category' => $category ? ['id' => (string) $category->id, 'name' => (string) $category->name] : null,
            'badge' => (string) ($item->badge ?? ''),
            'price' => (float) ($item->price ?? 0),
            'mrp' => (float) ($item->mrp ?? 0),
            'discount' => (float) ($item->discount ?? 0),
            'stock' => (int) ($item->stock ?? 0),
            'short_description' => (string) ($item->short_description ?? ''),
            'long_description' => (string) ($item->long_description ?? ''),
            'highlights' => $this->splitHighlights((string) ($item->highlights ?? '')),
            'image' => (string) (($primaryImage['image_url'] ?? '') ?: ($item->image ?? '')),
            'gallery_images' => $gallery,
            'is_active' => (bool) ($item->is_active ?? false),
            'is_featured' => (bool) ($item->is_featured ?? false),
            'created_at' => optional($item->created_at)->toISOString(),
        ];
    }

    private function categoryExists(string $id): bool
    {
        return ProductCategory::where('_id', $id)->exists();
    }

    private function buildUniqueSlug(string $seed, ?string $ignoreId = null): string
    {
        $base = Str::slug($seed);
        $root = $base !== '' ? $base : 'product';
        $slug = $root;
        $suffix = 1;

        while (true) {
            $query = Product::where('slug', $slug);
            if ($ignoreId) {
                $query->where('_id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                return $slug;
            }

            $slug = $root . '-' . $suffix;
            $suffix++;
        }
    }

    private function normalizeHighlights(?string $value): string
    {
        $parts = collect(explode(',', (string) $value))
            ->map(fn ($part) => trim($part))
            ->filter()
            ->unique()
            ->values();

        return $parts->implode(', ');
    }

    private function splitHighlights(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($part) => trim($part))
            ->filter()
            ->values()
            ->all();
    }

    private function uploadImage(Request $request, string $field): string
    {
        $file = $request->file($field);
        $destination = public_path('uploads/products');

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return url('uploads/products/' . $filename);
    }

    private function deleteImageIfExists(string $url): void
    {
        $path = public_path(parse_url($url, PHP_URL_PATH));
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
