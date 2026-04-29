<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PressLogoController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {
            $logos = PressLogo::orderBy('order')->get();

            $data = $logos->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'link' => $item->link,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'order' => $item->order,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data, 'Press Logos fetched');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
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
                'link' => 'nullable|url|max:500',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('press_logos', 'public');
            }

            $logo = PressLogo::create($data);

            return ApiResponse::success($logo, 'Created successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
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
            $logo = PressLogo::find($id);

            if (!$logo) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($logo);

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
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
            $logo = PressLogo::find($id);

            if (!$logo) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'link' => 'nullable|url|max:500',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($logo->image) {
                    Storage::disk('public')->delete($logo->image);
                }
                $data['image'] = $request->file('image')->store('press_logos', 'public');
            }

            $logo->update($data);

            return ApiResponse::success($logo, 'Updated successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
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
            $logo = PressLogo::find($id);

            if (!$logo) {
                return ApiResponse::error('Not found', [], 404);
            }

            if ($logo->image) {
                Storage::disk('public')->delete($logo->image);
            }

            $logo->delete();

            return ApiResponse::success([], 'Deleted successfully');

                } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}





