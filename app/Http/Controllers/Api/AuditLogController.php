<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        try {

            $query = AuditLog::query();

            // 🔹 Filter by module
            if ($request->filled('module')) {
                $query->where('module', $request->module);
            }

            // 🔹 Filter by admin/user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // 🔹 Filter by date range
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('created_at', [
                    $request->from_date,
                    $request->to_date
                ]);
            }

            // 🔹 Sorting latest first
            $query->latest();

            // 🔹 Pagination
            $logs = $query->paginate($request->get('per_page', 10));

            return ApiResponse::success($logs, 'Audit logs fetched successfully');

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch audit logs', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

