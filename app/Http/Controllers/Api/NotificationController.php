<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', [], 401);
            }

            $notifications = $user->notifications()->latest()->paginate((int) $request->get('per_page', 10));
            $notifications->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => data_get($item->data, 'title', 'Notification'),
                    'message' => data_get($item->data, 'message', ''),
                    'type' => data_get($item->data, 'type', 'info'),
                    'is_read' => !is_null($item->read_at),
                    'created_at' => optional($item->created_at)->format('Y-m-d H:i:s'),
                ];
            });

            return ApiResponse::success($notifications, 'Notifications fetched successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch notifications', ['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, string $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', [], 401);
            }

            $notification = $user->notifications()->where('id', $id)->first();

            if (!$notification) {
                return ApiResponse::error('Notification not found', [], 404);
            }

            return ApiResponse::success([
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notification'),
                'message' => data_get($notification->data, 'message', ''),
                'type' => data_get($notification->data, 'type', 'info'),
                'is_read' => !is_null($notification->read_at),
                'created_at' => optional($notification->created_at)->format('Y-m-d H:i:s'),
            ], 'Notification fetched successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch notification', ['error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead(Request $request, string $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', [], 401);
            }

            $notification = $user->notifications()->where('id', $id)->first();

            if (!$notification) {
                return ApiResponse::error('Notification not found', [], 404);
            }

            if (is_null($notification->read_at)) {
                $notification->markAsRead();
            }

            return ApiResponse::success([
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notification'),
                'message' => data_get($notification->data, 'message', ''),
                'type' => data_get($notification->data, 'type', 'info'),
                'is_read' => true,
                'created_at' => optional($notification->created_at)->format('Y-m-d H:i:s'),
            ], 'Notification marked as read');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to mark notification as read', ['error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', [], 401);
            }

            $user->unreadNotifications->markAsRead();

            return ApiResponse::success([], 'All notifications marked as read');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to mark all notifications as read', ['error' => $e->getMessage()], 500);
        }
    }
}
