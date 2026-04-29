<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ApiResponse;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            $payload = $request->validate([
                'order_reference' => 'nullable|string|max:100',
                'shipping' => 'required|array',
                'shipping.firstName' => 'required|string|max:100',
                'shipping.lastName' => 'required|string|max:100',
                'shipping.email' => 'required|email',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:1',
            ]);

            $orderReference = $payload['order_reference'] ?? (string) Str::uuid();
            $existingOrder = Order::where('order_reference', $orderReference)->first();
            if ($existingOrder) {
                return ApiResponse::success($existingOrder, 'Order already exists');
            }

            $order = Order::create([
                'order_id' => 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5((string) microtime(true)), 0, 5)),
                'order_reference' => $orderReference,
                'email' => $payload['shipping']['email'],
                'customer_name' => trim($payload['shipping']['firstName'] . ' ' . $payload['shipping']['lastName']),
                'purchase_date' => now(),
                'shipping' => $payload['shipping'],
                'items' => $payload['items'],
                'total' => (float) $payload['total'],
                'payment' => 'pending',
                'status' => Order::STATUS_PENDING,
            ]);

            return ApiResponse::success($order, 'Order created');
        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Exception $e) {
            return ApiResponse::error('Order create failed', ['error' => $e->getMessage()]);
        }
    }

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
                'items' => $order->items ?? [],
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
                'status' => 'required|in:pending,paid,failed,cancelled',
            ]);

            if (!$order->canTransitionTo((string) $data['status'])) {
                return ApiResponse::error('Invalid status transition', ['status' => ['Transition is not allowed']], 409);
            }

            $order->update($data);

            $responseData = [
                'id' => $order->id,
                'order_id' => $order->order_id,
                'customer_name' => $order->customer_name,
                'status' => $order->status,
            ];

            return ApiResponse::success($responseData, 'Order status updated successfully');

        } catch (ValidationException $e) {
            return ApiResponse::validation($e->validator);
        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }
}


