<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Blog::query();
            $isAdminView = $request->boolean('all', false);

            if (!$isAdminView) {
                $query->where('is_published', true);
            }

            if ($request->filled('category')) {
                $query->where('category', (string) $request->category);
            }

            $limit = min(max((int) $request->query('limit', 10), 1), 50);
            $blogs = $query
                ->orderBy('publish_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn ($item) => $this->transformBlog($item))
                ->values();

            return ApiResponse::success($blogs, 'Blogs fetched successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'category' => 'required|string|max:100',
                'excerpt' => 'nullable|string',
                'body' => 'required|string',
                'author' => 'required|string|max:255',
                'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_published' => 'required|boolean',
                'is_featured' => 'required|boolean',
                'publish_date' => 'nullable|date',
            ]);

            $data['slug'] = $this->buildUniqueSlug((string) ($data['slug'] ?? $data['title']));

            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $destination = public_path('uploads/blogs');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);
                $data['featured_image'] = url('uploads/blogs/' . $filename);
            }

            $blog = Blog::create($data);

            return ApiResponse::success($this->transformBlog($blog), 'Blog created successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    public function show(string $identifier)
    {
        try {
            $blog = Blog::find($identifier);
            if (!$blog) {
                $blog = Blog::where('slug', $identifier)->first();
            }

            if (!$blog) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($this->transformBlog($blog));
        } catch (Throwable $e) {
            return ApiResponse::error('Error', ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $blog = Blog::find($id);
            if (!$blog) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'category' => 'required|string|max:100',
                'excerpt' => 'nullable|string',
                'body' => 'required|string',
                'author' => 'required|string|max:255',
                'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'is_published' => 'required|boolean',
                'is_featured' => 'required|boolean',
                'publish_date' => 'nullable|date',
            ]);

            $data['slug'] = $this->buildUniqueSlug((string) ($data['slug'] ?? $data['title']), (string) $blog->id);

            if ($request->hasFile('featured_image')) {
                if (!empty($blog->featured_image)) {
                    $oldPath = public_path(parse_url($blog->featured_image, PHP_URL_PATH));
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $file = $request->file('featured_image');
                $destination = public_path('uploads/blogs');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);
                $data['featured_image'] = url('uploads/blogs/' . $filename);
            }

            $blog->update($data);

            return ApiResponse::success($this->transformBlog($blog->fresh() ?? $blog), 'Updated successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $blog = Blog::find($id);
            if (!$blog) {
                return ApiResponse::error('Not found', [], 404);
            }

            if (!empty($blog->featured_image)) {
                $path = public_path(parse_url($blog->featured_image, PHP_URL_PATH));
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $blog->delete();

            return ApiResponse::success([], 'Deleted successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }

    private function transformBlog(Blog $item): array
    {
        return [
            'id' => (string) ($item->id ?? $item->_id),
            'title' => (string) $item->title,
            'slug' => (string) $item->slug,
            'category' => (string) ($item->category ?? ''),
            'excerpt' => (string) ($item->excerpt ?? ''),
            'body' => (string) ($item->body ?? ''),
            'author' => (string) ($item->author ?? ''),
            'image' => (string) ($item->featured_image ?? ''),
            'featured_image' => (string) ($item->featured_image ?? ''),
            'published' => (bool) ($item->is_published ?? false),
            'is_published' => (bool) ($item->is_published ?? false),
            'featured' => (bool) ($item->is_featured ?? false),
            'is_featured' => (bool) ($item->is_featured ?? false),
            'publish_date' => optional($item->publish_date)->format('Y-m-d'),
            'created_at' => optional($item->created_at)->format('Y-m-d'),
        ];
    }

    private function buildUniqueSlug(string $seed, ?string $ignoreId = null): string
    {
        $base = Str::slug($seed);
        $root = $base !== '' ? $base : 'blog-post';
        $slug = $root;
        $suffix = 1;

        while (true) {
            $query = Blog::where('slug', $slug);
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
}
