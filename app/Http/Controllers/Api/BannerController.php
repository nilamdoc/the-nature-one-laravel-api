<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class BannerController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {
            $banners = Banner::orderBy('created_at', 'desc')->get();

            $data = $banners->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'cta_text' => $item->cta_text,
                    'cta_link' => $item->cta_link,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data, 'Banners fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 STORE
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'cta_text' => 'nullable|string|max:255',
                'cta_link' => 'nullable|url|max:500',
                'is_active' => 'required|boolean',
            ]);

            $banner = Banner::create($data);

            return ApiResponse::success($banner, 'Created successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Create failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 SHOW
     */
    public function show($id)
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($banner);

        } catch (\Exception $e) {
            return ApiResponse::error('Error', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 UPDATE
     */
    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'cta_text' => 'nullable|string|max:255',
                'cta_link' => 'nullable|url|max:500',
                'is_active' => 'required|boolean',
            ]);

            $banner->update($data);

            return ApiResponse::success($banner, 'Updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 DELETE
     */
    public function destroy($id)
    {
        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return ApiResponse::error('Not found', [], 404);
            }

            $banner->delete();

            return ApiResponse::success([], 'Deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}