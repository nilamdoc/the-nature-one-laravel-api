<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;

class OrderController extends Controller
{
    /**
     * 🔹 LIST ORDERS (Paginated)
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);

            $orders = Order::orderBy('purchase_date', 'desc')->paginate($perPage);

            $data = $orders->getCollection()->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'customer_name' => $order->customer_name,
                    'purchase_date' => $order->purchase_date->format('Y-m-d H:i:s'),
                    'total' => $order->total,
                    'payment' => $order->payment,
                    'status' => $order->status,
                ];
            });

            return ApiResponse::success([
                'data' => $data,
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ], 'Orders fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 VIEW ORDER DETAILS
     */
    public function show($id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return ApiResponse::error('Order not found', [], 404);
            }

            $data = [
                'id' => $order->id,
                'order_id' => $order->order_id,
                'customer_name' => $order->customer_name,
                'purchase_date' => $order->purchase_date->format('Y-m-d H:i:s'),
                // items is already an array thanks to $casts
                'items' => '-',
                'total' => $order->total,
                'payment' => $order->payment,
                'status' => $order->status,
            ];

            return ApiResponse::success($data, 'Order details fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 UPDATE ORDER STATUS
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return ApiResponse::error('Order not found', [], 404);
            }

            $data = $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $order->update($data);

            $responseData = [
                'id' => $order->id,
                'order_id' => $order->order_id,
                'customer_name' => $order->customer_name,
                'status' => $order->status,
            ];

            return ApiResponse::success($responseData, 'Order status updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }
}