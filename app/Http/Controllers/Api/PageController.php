<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PageController extends Controller
{
    /**
     * 🔹 LIST PAGES
     */
    public function index()
    {
        try {

            $pages = Page::latest()->paginate(10);

            $pages->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'seo_title' => $item->seo_title,
                    'created_at' => $item->created_at->format('Y-m-d'),
                ];
            });

            return ApiResponse::success($pages, 'Pages fetched successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 STORE PAGE
     */
    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'content' => 'required|string',
                'seo_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
            ]);

            // 🔥 Auto slug
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            // 🔥 Unique slug (important)
            $exists = Page::where('slug', $data['slug'])->exists();
            if ($exists) {
                $data['slug'] .= '-' . time();
            }

            $page = Page::create($data);

            return ApiResponse::success($page, 'Page created successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Create failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 SHOW PAGE
     */
    public function show($id)
    {
        try {

            $page = Page::find($id);

            if (!$page) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($page);

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Error', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 UPDATE PAGE
     */
    public function update(Request $request, $id)
    {
        try {

            $page = Page::find($id);

            if (!$page) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'content' => 'required|string',
                'seo_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
            ]);

            // 🔹 Slug update
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            $exists = Page::where('slug', $data['slug'])
                ->where('_id', '!=', $id)
                ->exists();

            if ($exists) {
                $data['slug'] .= '-' . time();
            }

            $page->update($data);

            return ApiResponse::success($page, 'Updated successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Update failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 DELETE PAGE
     */
    public function destroy($id)
    {
        try {

            $page = Page::find($id);

            if (!$page) {
                return ApiResponse::error('Not found', [], 404);
            }

            $page->delete();

            return ApiResponse::success([], 'Deleted successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔥 GET PAGE BY SLUG (Frontend Use)
     */
    public function getBySlug($slug)
    {
        try {

            $page = Page::where('slug', $slug)->first();

            if (!$page) {
                return ApiResponse::error('Page not found', [], 404);
            }

            return ApiResponse::success([
                'title' => $page->title,
                'content' => $page->content,
                'seo_title' => $page->seo_title,
                'meta_description' => $page->meta_description,
            ]);

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}





