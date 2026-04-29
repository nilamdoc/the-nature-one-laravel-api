<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Throwable;

class SliderController extends Controller
{
    public function index()
    {
        try {
            $slides = HeroSlide::where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->get()
                ->map(fn ($item) => $this->transform($item))
                ->values();

            return ApiResponse::success($slides, 'Sliders fetched');
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch sliders', ['error' => $e->getMessage()], 500);
        }
    }

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
            return ApiResponse::success($this->transform($slide), 'Slider created');
        } catch (Throwable $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, string $id)
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
            return ApiResponse::success($this->transform($slide->fresh() ?? $slide), 'Slider updated');
        } catch (Throwable $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $slide = HeroSlide::find($id);
            if (!$slide) {
                return ApiResponse::error('Not found', [], 404);
            }

            if (!empty($slide->image)) {
                $path = public_path(parse_url($slide->image, PHP_URL_PATH));
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $slide->delete();
            return ApiResponse::success([], 'Slider deleted');
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }

    private function transform(HeroSlide $item): array
    {
        return [
            'id' => (string) ($item->id ?? $item->_id),
            'title' => (string) ($item->headline ?? ''),
            'subtitle' => (string) ($item->subtitle ?? ''),
            'image' => (string) ($item->image ?? ''),
            'link' => (string) ($item->cta_link ?? '/shop'),
            'button_text' => (string) ($item->cta_text ?? 'Shop All'),
            'sort_order' => (int) ($item->display_order ?? 0),
            'status' => (string) ($item->status ?? 'inactive'),
            'headline' => (string) ($item->headline ?? ''),
            'cta_text' => (string) ($item->cta_text ?? ''),
            'cta_link' => (string) ($item->cta_link ?? ''),
            'display_order' => (int) ($item->display_order ?? 0),
            'show_text_overlay' => (bool) ($item->show_text_overlay ?? true),
        ];
    }
}
