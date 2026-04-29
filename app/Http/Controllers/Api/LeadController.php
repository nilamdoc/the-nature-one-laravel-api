<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ApiResponse;

class LeadController extends Controller
{
    /**
     * 🔹 LIST + STATISTICS
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            $leads = Lead::orderBy('date', 'desc')->paginate($perPage);

            $data = $leads->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'source' => $item->source,
                    'date' => $item->date->format('Y-m-d H:i:s'),
                    'status' => $item->status,
                ];
            });

            // 🔹 Statistics
            $stats = [
                'total' => Lead::count(),
                'new' => Lead::where('status', 'New')->count(),
                'contacted' => Lead::where('status', 'Contacted')->count(),
                'converted' => Lead::where('status', 'Converted')->count(),
            ];

            return ApiResponse::success([
                'data' => $data,
                'stats' => $stats,
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
            ], 'Leads fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 SHOW LEAD DETAILS
     */
    public function show($id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return ApiResponse::error('Lead not found', [], 404);
            }

            $data = [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'source' => $lead->source,
                'date' => $lead->date->format('Y-m-d H:i:s'),
                'status' => $lead->status,
            ];

            return ApiResponse::success($data, 'Lead details fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Error', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 UPDATE STATUS
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return ApiResponse::error('Lead not found', [], 404);
            }

            $data = $request->validate([
                'status' => 'required|in:New,Contacted,Converted,Closed',
            ]);

            $lead->update($data);

            return ApiResponse::success([
                'id' => $lead->id,
                'name' => $lead->name,
                'status' => $lead->status,
            ], 'Lead status updated successfully');

        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}


