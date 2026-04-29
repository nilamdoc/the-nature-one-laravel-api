<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalUsers = $this->safeCount(\App\Models\User::class);
            $totalOrders = $this->safeCount(\App\Models\Order::class);
            $totalProducts = $this->safeCount(\App\Models\Product::class);
            $totalLeads = $this->safeCount(\App\Models\Lead::class);
            $totalSubscribers = $this->safeCount(\App\Models\Newsletter::class);
            $totalCategories = $this->safeCount(\App\Models\ProductCategory::class);
            $totalRevenue = $this->safeSum(\App\Models\Order::class, 'total');

            $recentLeads = $this->loadRecentLeads();
            $recentOrders = $this->loadRecentOrders();

            return ApiResponse::success([
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_leads' => $totalLeads,
                    'total_subscribers' => $totalSubscribers,
                    'total_categories' => $totalCategories,
                    'total_revenue' => $totalRevenue,
                ],
                'recent_leads' => $recentLeads,
                'recent_orders' => $recentOrders,
            ], 'Dashboard data fetched successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Dashboard failed', [
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function safeCount($modelClass): int
    {
        try {
            if (!class_exists($modelClass)) {
                return 0;
            }

            $model = new $modelClass;

            if (method_exists($model, 'raw')) {
                return (int) $model->raw(function ($collection) {
                    return $collection->countDocuments();
                });
            }

            return (int) $modelClass::query()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function safeSum($modelClass, string $field): float
    {
        try {
            if (!class_exists($modelClass)) {
                return 0;
            }

            return (float) $modelClass::query()->sum($field);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function loadRecentLeads(): array
    {
        if (!class_exists(\App\Models\Lead::class)) {
            return [];
        }

        try {
            return \App\Models\Lead::orderBy('_id', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->_id ?? $item->id ?? null,
                        'name' => $item->name ?? 'Guest',
                        'email' => $item->email ?? null,
                        'status' => strtolower((string) ($item->status ?? 'new')),
                        'date' => isset($item->date)
                            ? optional($item->date)->format('Y-m-d H:i')
                            : (isset($item->created_at) ? optional($item->created_at)->format('Y-m-d H:i') : null),
                    ];
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function loadRecentOrders(): array
    {
        if (!class_exists(\App\Models\Order::class)) {
            return [];
        }

        try {
            return \App\Models\Order::orderBy('_id', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->_id ?? $item->id ?? null,
                        'order_no' => $item->order_no ?? $item->order_id ?? null,
                        'customer' => $item->customer_name ?? 'Customer',
                        'amount' => (float) ($item->total ?? 0),
                        'status' => strtolower((string) ($item->status ?? 'pending')),
                        'date' => isset($item->purchase_date)
                            ? optional($item->purchase_date)->format('Y-m-d H:i')
                            : (isset($item->created_at) ? optional($item->created_at)->format('Y-m-d H:i') : null),
                    ];
                })
                ->values()
                ->all();
        } catch (\Exception $e) {
            return [];
        }
    }
}
