<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ApiResponse;

class CertificationController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {
            $certifications = Certification::orderBy('order')->get();

            $data = $certifications->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'order' => $item->order,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data, 'Certifications fetched');

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
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('certifications', 'public');
            }

            $certification = Certification::create($data);

            return ApiResponse::success($certification, 'Created successfully');

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
            $certification = Certification::find($id);

            if (!$certification) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($certification);

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
            $certification = Certification::find($id);

            if (!$certification) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($certification->image) {
                    Storage::disk('public')->delete($certification->image);
                }
                $data['image'] = $request->file('image')->store('certifications', 'public');
            }

            $certification->update($data);

            return ApiResponse::success($certification, 'Updated successfully');

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
            $certification = Certification::find($id);

            if (!$certification) {
                return ApiResponse::error('Not found', [], 404);
            }

            if ($certification->image) {
                Storage::disk('public')->delete($certification->image);
            }

            $certification->delete();

            return ApiResponse::success([], 'Deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}