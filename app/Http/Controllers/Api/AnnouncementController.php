<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Announcement;
use Throwable;


class AnnouncementController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {

            $announcements = Announcement::orderBy('order')->get();

            $data = $announcements->map(function ($item) {
                return [
                    'id' => $item->_id,
                    'text' => $item->text,
                    'order' => $item->order,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data, 'Announcements fetched');

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
                'text' => 'required|string|max:500',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
            ]);

            $announcement = Announcement::create($data);

            return ApiResponse::success($announcement, 'Created successfully');

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

            $announcement = Announcement::find($id);

            if (!$announcement) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($announcement);

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

            $announcement = Announcement::find($id);

            if (!$announcement) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'text' => 'required|string|max:500',
                'order' => 'required|integer',
                'is_active' => 'required|boolean',
            ]);

            $announcement->update($data);

            return ApiResponse::success($announcement, 'Updated successfully');

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

            $announcement = Announcement::find($id);

            if (!$announcement) {
                return ApiResponse::error('Not found', [], 404);
            }

            $announcement->delete();

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





