<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use App\Exports\NewsletterExport;
use Maatwebsite\Excel\Facades\Excel;

class NewsletterController extends Controller
{
    /**
     * 🔹 SUBSCRIBE EMAIL
     */
    public function subscribe(Request $request)
    {
        try {

            // ✅ Validation
            $data = $request->validate([
                'email' => 'required|email|max:255'
            ]);

            $email = strtolower(trim($data['email']));

            // 🔹 Check existing subscriber
            $subscriber = Newsletter::where('email', $email)->first();

            // 🔥 If already exists
            if ($subscriber) {

                // 👉 If unsubscribed → Reactivate
                if ($subscriber->status === 'unsubscribed') {
                    $subscriber->update([
                        'status' => 'active'
                    ]);

                    return ApiResponse::success([], 'You have been resubscribed successfully');
                }

                // 👉 Already active
                return ApiResponse::success([], 'You are already subscribed');
            }

            // 🔥 Create new subscriber
            Newsletter::create([
                'email' => $email,
                'status' => 'active'
            ]);

            return ApiResponse::success([], 'Subscribed successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Subscription failed', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * 🔹 GET NEWSLETTER LIST
     */
    public function index(Request $request)
    {
        try {

            $query = Newsletter::query();

            // 🔹 Stats
            $totalSubscribers = Newsletter::count();
            $activeSubscribers = Newsletter::where('status', 'active')->count();

            // 🔹 List with pagination
            $subscribers = $query->latest()->paginate(10);

            // 🔹 Transform for UI
            $subscribers->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'email' => $item->email,
                    'status' => ucfirst($item->status),
                    'date' => $item->created_at->format('Y-m-d'),
                ];
            });

            return ApiResponse::success([
                'total_subscribers' => $totalSubscribers,
                'active_subscribers' => $activeSubscribers,
                'subscribers' => $subscribers
            ], 'Newsletter data fetched successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch newsletter data', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 DELETE SUBSCRIBER
     */
    public function destroy($id)
    {
        try {

            $subscriber = Newsletter::find($id);

            if (!$subscriber) {
                return ApiResponse::error('Subscriber not found', [], 404);
            }

            $subscriber->delete();

            return ApiResponse::success([], 'Subscriber deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function export(Request $request)
    {
        try {

            $filters = $request->only(['status']);

            return Excel::download(
                new NewsletterExport($filters),
                'newsletter.csv'
            );

        } catch (\Exception $e) {
            return ApiResponse::error('Export failed', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unsubscribe(Request $request)
    {
        try {

            $email = $request->query('email');

            if (!$email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email is required'
                ], 400);
            }

            $subscriber = \App\Models\Newsletter::where('email', $email)->first();

            if (!$subscriber) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subscriber not found'
                ], 404);
            }

            // 🔥 Already unsubscribed check
            if ($subscriber->status === 'unsubscribed') {
                return response()->json([
                    'status' => true,
                    'message' => 'You are already unsubscribed'
                ]);
            }

            // 🔥 Update status
            $subscriber->update([
                'status' => 'unsubscribed'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'You have been unsubscribed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}