<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;

class DashboardController extends Controller
{
    public function index()
    {
        try {

            /**
             * ============================
             * 🔹 SAFE COUNTS (MongoDB)
             * ============================
             */
            $totalUsers = $this->safeCount(\App\Models\User::class);
            $totalOrders = $this->safeCount(\App\Models\Order::class);
            $totalProducts = $this->safeCount(\App\Models\Product::class);
            $totalSubscribers = $this->safeCount(\App\Models\Newsletter::class);

            /**
             * ============================
             * 🔹 RECENT LEADS
             * ============================
             */
            $recentLeads = [];

            if (class_exists(\App\Models\Newsletter::class)) {
                try {
                    $recentLeads = \App\Models\Newsletter::orderBy('_id', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->_id ?? null,
                                'name' => $item->name ?? 'Guest',
                                'email' => $item->email ?? null,
                                'status' => ucfirst($item->status ?? 'active'),
                                'date' => isset($item->created_at)
                                    ? optional($item->created_at)->format('Y-m-d H:i')
                                    : null,
                            ];
                        });
                } catch (\Exception $e) {
                    $recentLeads = [];
                }
            }

            /**
             * ============================
             * 🔹 RECENT ORDERS
             * ============================
             */
            $recentOrders = [];

            if (class_exists(\App\Models\Order::class)) {
                try {
                    $recentOrders = \App\Models\Order::orderBy('_id', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->_id ?? null,
                                'order_no' => $item->order_no ?? null,
                                'amount' => $item->total ?? 0,
                                'status' => ucfirst($item->status ?? 'pending'),
                                'date' => isset($item->created_at)
                                    ? optional($item->created_at)->format('Y-m-d H:i')
                                    : null,
                            ];
                        });
                } catch (\Exception $e) {
                    $recentOrders = [];
                }
            }

            /**
             * ============================
             * 🔹 FINAL RESPONSE
             * ============================
             */
            return ApiResponse::success([
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_subscribers' => $totalSubscribers,
                ],
                'recent_leads' => $recentLeads,
                'recent_orders' => $recentOrders,
            ], 'Dashboard data fetched successfully');

        } catch (\Exception $e) {

            return ApiResponse::error('Dashboard failed', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================
     * 🔥 MONGODB SAFE COUNT
     * ============================
     */
    private function safeCount($modelClass)
    {
        try {

            if (!class_exists($modelClass)) {
                return 0;
            }

            $model = new $modelClass;

            return $model->raw(function ($collection) {
                return $collection->countDocuments();
            });

        } catch (\Exception $e) {
            return 0;
        }
    }
}