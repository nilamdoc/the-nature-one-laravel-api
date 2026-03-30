<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * 🔹 LIST FAQ
     */
    public function index(Request $request)
    {
        try {
            $query = Faq::query();

            // Optional filter
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            $faqs = $query->latest()->paginate(10);

            // Transform for UI
            $faqs->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'question' => $item->question,
                    'answer' => $item->answer,
                    'category' => $item->category,
                    'status' => ucfirst($item->status),
                    'created_at' => $item->created_at->format('Y-m-d'),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'FAQ list fetched successfully',
                'data' => $faqs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch FAQs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 STORE FAQ
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'question' => 'required|string|max:500',
                'answer' => 'required|string',
                'category' => 'required|string|max:100',
                'status' => 'required|in:active,inactive',
            ]);

            $faq = Faq::create($data);

            return response()->json([
                'status' => true,
                'message' => 'FAQ created successfully',
                'data' => $faq
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Create failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 SHOW SINGLE FAQ
     */
    public function show($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json([
                    'status' => false,
                    'message' => 'FAQ not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $faq
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching FAQ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 UPDATE FAQ
     */
    public function update(Request $request, $id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json([
                    'status' => false,
                    'message' => 'FAQ not found'
                ], 404);
            }

            $data = $request->validate([
                'question' => 'required|string|max:500',
                'answer' => 'required|string',
                'category' => 'required|string|max:100',
                'status' => 'required|in:active,inactive',
            ]);

            $faq->update($data);

            return response()->json([
                'status' => true,
                'message' => 'FAQ updated successfully',
                'data' => $faq
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 DELETE FAQ
     */
    public function destroy($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json([
                    'status' => false,
                    'message' => 'FAQ not found'
                ], 404);
            }

            $faq->delete();

            return response()->json([
                'status' => true,
                'message' => 'FAQ deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}