<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

        } catch (\Throwable $e) {
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

        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * CREATE
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'rate' => 'required|numeric',
                'is_active' => 'required|boolean',
            ]);

            $tax = Tax::create($data);

            return ApiResponse::success($tax, 'Tax rule created successfully');
        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Throwable $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        try {
            $tax = Tax::find($id);

            if (!$tax) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'name' => 'required|string',
                'rate' => 'required|numeric',
                'is_active' => 'required|boolean',
            ]);

            $tax->update($data);

            return ApiResponse::success($tax, 'Tax rule updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Throwable $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        try {
            $tax = Tax::find($id);

            if (!$tax) {
                return ApiResponse::error('Not found', [], 404);
            }

            $tax->delete();

            return ApiResponse::success([], 'Tax rule deleted successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }
}



