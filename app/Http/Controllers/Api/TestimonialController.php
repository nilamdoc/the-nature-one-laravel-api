<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class TestimonialController extends Controller
{
    /**
     * 🔹 LIST TESTIMONIALS
     */
    public function index()
    {
        try {
            $testimonials = Testimonial::latest()->paginate(10);
            $testimonials->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'name' => $item->name,
                    'designation' => $item->designation,
                    'message' => $item->message,
                    'rating' => $item->rating,
                    'image' => $item->image,
                    'status' => ucfirst($item->status),
                    'date' => $item->created_at->format('Y-m-d'),
                ];
            });
            return ApiResponse::success($testimonials, 'Testimonials fetched');
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
                'name' => 'required|string|max:255',
                'designation' => 'required|string|max:255',
                'message' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status' => 'required|in:active,inactive',
            ]);
            // 🔥 Upload image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                // 🔹 Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                // 🔹 Move to public/testimonials folder
                $file->move(public_path('uploads/testimonials'), $filename);
                // 🔹 Save full URL
                $data['image'] = url('uploads/testimonials/' . $filename);
            }
            $testimonial = Testimonial::create($data);
            return ApiResponse::success($testimonial, 'Created successfully');
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
            $testimonial = Testimonial::find($id);
            if (!$testimonial) {
                return ApiResponse::error('Not found', [], 404);
            }
            return ApiResponse::success($testimonial);
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
            $testimonial = Testimonial::find($id);
            if (!$testimonial) {
                return ApiResponse::error('Not found', [], 404);
            }
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'designation' => 'required|string|max:255',
                'message' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'status' => 'required|in:active,inactive',
            ]);
            if ($request->hasFile('image')) {
                // 🔥 Delete old image
                if (!empty($testimonial->image)) {
                    $oldPath = public_path(parse_url($testimonial->image, PHP_URL_PATH));
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $file = $request->file('image');
                $destination = public_path('uploads/testimonials');
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);
                $data['image'] = url('uploads/testimonials/' . $filename);
            }
            $testimonial->update($data);
            return ApiResponse::success($testimonial, 'Updated successfully');
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
            $testimonial = Testimonial::find($id);
            if (!$testimonial) {
                return ApiResponse::error('Not found', [], 404);
            }
            $testimonial->delete();
            return ApiResponse::success([], 'Deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}