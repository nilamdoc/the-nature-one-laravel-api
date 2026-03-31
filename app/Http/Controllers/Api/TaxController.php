<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class TaxController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {

            $taxes = Tax::all();

            $data = $taxes->map(function ($item) {
                return [
                    'id' => $item->_id,
                    'name' => $item->name,
                    'label' => $item->name . ' — ' . $item->rate . '%',
                    'rate' => $item->rate,
                    'is_active' => $item->is_active,
                ];
            });

            return ApiResponse::success($data);

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 STORE / UPDATE (same API)
     */
    public function save(Request $request)
    {
        try {

            $data = $request->validate([
                'name' => 'required|string',
                'rate' => 'required|numeric',
                'is_active' => 'required|boolean',
            ]);

            $tax = Tax::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            return ApiResponse::success($tax, 'Saved successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }
}