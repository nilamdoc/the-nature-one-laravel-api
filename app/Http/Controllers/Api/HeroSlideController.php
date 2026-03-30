<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class HeroSlideController extends Controller
{
    /**
     * 🔹 LIST SLIDES
     */
    public function index()
    {
        try {

            $slides = HeroSlide::orderBy('display_order')->get();

            $slides = $slides->map(function ($item) {
                return [
                    'id' => $item->_id,
                    'headline' => $item->headline,
                    'subtitle' => $item->subtitle,
                    'cta_text' => $item->cta_text,
                    'cta_link' => $item->cta_link,
                    'image' => $item->image,
                    'display_order' => $item->display_order,
                    'show_text_overlay' => $item->show_text_overlay,
                    'status' => ucfirst($item->status),
                ];
            });

            return ApiResponse::success($slides, 'Slides fetched');

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
                'headline' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:500',
                'cta_text' => 'nullable|string|max:100',
                'cta_link' => 'nullable|url',
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'display_order' => 'required|integer',
                'show_text_overlay' => 'required|boolean',
                'status' => 'required|in:active,inactive',
            ]);

            // 🔥 Upload image (public folder)
            if ($request->hasFile('image')) {

                $file = $request->file('image');
                $destination = public_path('uploads/hero-slides');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);

                $data['image'] = url('uploads/hero-slides/' . $filename);
            }

            $slide = HeroSlide::create($data);

            return ApiResponse::success($slide, 'Slide created');

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

            $slide = HeroSlide::find($id);

            if (!$slide) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($slide);

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

            $slide = HeroSlide::find($id);

            if (!$slide) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'headline' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:500',
                'cta_text' => 'nullable|string|max:100',
                'cta_link' => 'nullable|url',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'display_order' => 'required|integer',
                'show_text_overlay' => 'required|boolean',
                'status' => 'required|in:active,inactive',
            ]);

            // 🔥 Replace image
            if ($request->hasFile('image')) {

                if (!empty($slide->image)) {
                    $oldPath = public_path(parse_url($slide->image, PHP_URL_PATH));
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $file = $request->file('image');
                $destination = public_path('uploads/hero-slides');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);

                $data['image'] = url('uploads/hero-slides/' . $filename);
            }

            $slide->update($data);

            return ApiResponse::success($slide, 'Updated successfully');

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

            $slide = HeroSlide::find($id);

            if (!$slide) {
                return ApiResponse::error('Not found', [], 404);
            }

            // 🔥 Delete image
            if (!empty($slide->image)) {
                $path = public_path(parse_url($slide->image, PHP_URL_PATH));
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $slide->delete();

            return ApiResponse::success([], 'Deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}