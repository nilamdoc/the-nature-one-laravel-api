<?php

namespace App\Http\Controllers\Api;

use App\Exports\NewsletterExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $email = strtolower(trim($data['email']));
            $subscriber = Newsletter::where('email', $email)->first();

            if ($subscriber) {
                if (strtolower((string) $subscriber->status) === 'unsubscribed') {
                    $subscriber->update(['status' => 'active']);
                    return ApiResponse::success([], 'You have been resubscribed successfully');
                }

                return ApiResponse::success([], 'You are already subscribed');
            }

            Newsletter::create([
                'email' => $email,
                'status' => 'active',
            ]);

            return ApiResponse::success([], 'Subscribed successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Subscription failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Newsletter::query();

            if ($request->filled('status')) {
                $query->where('status', strtolower((string) $request->status));
            }

            $subscribers = $query->latest()->paginate(10);
            $subscribers->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->_id,
                    'email' => $item->email,
                    'status' => strtolower((string) $item->status),
                    'date' => optional($item->created_at)->format('Y-m-d'),
                ];
            });

            return ApiResponse::success([
                'total_subscribers' => Newsletter::count(),
                'active_subscribers' => Newsletter::where('status', 'active')->count(),
                'subscribers' => $subscribers,
            ], 'Newsletter data fetched successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch newsletter data', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $subscriber = Newsletter::find($id);

            if (!$subscriber) {
                return ApiResponse::error('Subscriber not found', [], 404);
            }

            $subscriber->delete();

            return ApiResponse::success([], 'Subscriber deleted successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $filters = $request->only(['status']);
            return Excel::download(new NewsletterExport($filters), 'newsletter.csv');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Export failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function unsubscribe(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $subscriber = Newsletter::where('email', strtolower(trim($data['email'])))->first();

            if (!$subscriber) {
                return ApiResponse::error('Subscriber not found', [], 404);
            }

            if (strtolower((string) $subscriber->status) === 'unsubscribed') {
                return ApiResponse::success([], 'You are already unsubscribed');
            }

            $subscriber->update(['status' => 'unsubscribed']);

            return ApiResponse::success([], 'You have been unsubscribed successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    }
}
