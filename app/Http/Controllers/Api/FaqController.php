<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Faq::query();

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            $faqs = $query->latest()->paginate(10);

            $faqs->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'question' => $item->question,
                    'answer' => $item->answer,
                    'category' => $item->category,
                    'status' => strtolower((string) $item->status),
                    'order' => (int) ($item->order ?? 0),
                    'created_at' => optional($item->created_at)->format('Y-m-d'),
                ];
            });

            return ApiResponse::success($faqs, 'FAQ list fetched successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch FAQs', ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'question' => 'required|string|max:500',
                'answer' => 'required|string',
                'category' => 'required|string|max:100',
                'status' => 'required|in:active,inactive',
                'order' => 'nullable|integer|min:0',
            ]);

            $data['status'] = strtolower((string) $data['status']);
            $faq = Faq::create($data);

            return ApiResponse::success($faq, 'FAQ created successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return ApiResponse::error('FAQ not found', [], 404);
            }

            return ApiResponse::success([
                'id' => $faq->_id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'category' => $faq->category,
                'status' => strtolower((string) $faq->status),
                'order' => (int) ($faq->order ?? 0),
                'created_at' => optional($faq->created_at)->format('Y-m-d'),
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Error fetching FAQ', ['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return ApiResponse::error('FAQ not found', [], 404);
            }

            $data = $request->validate([
                'question' => 'required|string|max:500',
                'answer' => 'required|string',
                'category' => 'required|string|max:100',
                'status' => 'required|in:active,inactive',
                'order' => 'nullable|integer|min:0',
            ]);

            $data['status'] = strtolower((string) $data['status']);
            $faq->update($data);

            return ApiResponse::success($faq, 'FAQ updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return ApiResponse::error('FAQ not found', [], 404);
            }

            $faq->delete();

            return ApiResponse::success([], 'FAQ deleted successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()], 500);
        }
    }
}
