<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\ApiResponse;

class BlogController extends Controller
{
    /**
     * 🔹 LIST BLOGS
     */
    public function index(Request $request)
    {
        try {

            $query = Blog::query();

            // 🔹 Filters
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('is_published')) {
                $query->where('is_published', (bool)$request->is_published);
            }

            $blogs = $query->latest()->paginate(10);

            $blogs->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'category' => $item->category,
                    'excerpt' => $item->excerpt,
                    'author' => $item->author,
                    'image' => $item->featured_image,
                    'published' => $item->is_published,
                    'featured' => $item->is_featured,
                    'publish_date' => optional($item->publish_date)->format('Y-m-d'),
                    'created_at' => $item->created_at->format('Y-m-d'),
                ];
            });

            return ApiResponse::success($blogs, 'Blogs fetched successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 STORE BLOG
     */
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

            // 🔥 Auto slug
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            // 🔥 Upload Image (public folder)
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

            return ApiResponse::success($blog, 'Blog created successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Create failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 SHOW BLOG
     */
    public function show($id)
    {
        try {

            $blog = Blog::find($id);

            if (!$blog) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($blog);

        } catch (\Exception $e) {
            return ApiResponse::error('Error', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 UPDATE BLOG
     */
    public function update(Request $request, $id)
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

            // 🔹 Slug update
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            // 🔥 Replace Image
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

            return ApiResponse::success($blog, 'Updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 DELETE BLOG
     */
    public function destroy($id)
    {
        try {

            $blog = Blog::find($id);

            if (!$blog) {
                return ApiResponse::error('Not found', [], 404);
            }

            $blog->delete();

            return ApiResponse::success([], 'Deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}