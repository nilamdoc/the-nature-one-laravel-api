<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ApiResponse;

class ShippingZoneController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {

            $zones = ShippingZone::all();

            $data = $zones->map(function ($item) {
                return [
                    'id' => $item->_id,
                    'name' => $item->name,
                    'cities' => implode(', ', $item->cities),
                    'price' => '₹' . $item->price,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data);

        } catch (\Throwable $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
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
                'cities' => 'required|array',
                'price' => 'required|numeric',
                'is_active' => 'required|boolean',
            ]);

            $zone = ShippingZone::create($data);

            return ApiResponse::success($zone, 'Created successfully');

        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Throwable $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 UPDATE
     */
    public function update(Request $request, $id)
    {
        try {

            $zone = ShippingZone::find($id);

            if (!$zone) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'name' => 'required|string',
                'cities' => 'required|array',
                'price' => 'required|numeric',
                'is_active' => 'required|boolean',
            ]);

            $zone->update($data);

            return ApiResponse::success($zone, 'Updated successfully');

        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Throwable $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 DELETE
     */
    public function destroy($id)
    {
        try {

            $zone = ShippingZone::find($id);

            if (!$zone) {
                return ApiResponse::error('Not found', [], 404);
            }

            $zone->delete();

            return ApiResponse::success([], 'Deleted');

        } catch (\Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }
}


