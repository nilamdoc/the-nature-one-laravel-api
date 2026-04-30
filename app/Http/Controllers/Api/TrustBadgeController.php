<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use App\Models\TrustBadge;
use Illuminate\Validation\ValidationException;
use Throwable;

class TrustBadgeController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {

            $badges = TrustBadge::orderBy('order')->get();

            $data = $badges->map(function ($item) {
                return [
                    'id' => $item->_id,
                    'title' => $item->title,
                    'order' => $item->order,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data, 'Badges fetched');

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
                'title' => 'required|string|max:255',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
            ]);

            $badge = TrustBadge::create($data);

            return ApiResponse::success($badge, 'Created successfully');

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

            $badge = TrustBadge::find($id);

            if (!$badge) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($badge);

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

            $badge = TrustBadge::find($id);

            if (!$badge) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
            ]);

            $badge->update($data);

            return ApiResponse::success($badge, 'Updated successfully');

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

            $badge = TrustBadge::find($id);

            if (!$badge) {
                return ApiResponse::error('Not found', [], 404);
            }

            $badge->delete();

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





