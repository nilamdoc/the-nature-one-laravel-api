<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $database = DB::connection('mongodb')->getMongoDB();

        /**
         * =========================
         * ORDERS COLLECTION
         * =========================
         */
        $orders = $database->selectCollection('orders');

        // ✅ FIX: Clean existing NULL values (important before unique index)
        $orders->updateMany(
            ['order_reference' => null],
            ['$unset' => ['order_reference' => ""]]
        );

        // ✅ FIX: Use PARTIAL UNIQUE INDEX (ignore null/missing values)
        $orders->createIndex(
            ['order_reference' => 1],
            [
                'name' => 'orders_order_reference_unique',
                'unique' => true,
                'partialFilterExpression' => [
                    'order_reference' => ['$type' => 'string']
                ]
            ]
        );

        // Other indexes
        $orders->createIndex(['user_id' => 1], ['name' => 'orders_user_id_idx']);
        $orders->createIndex(
            ['status' => 1, 'purchase_date' => -1],
            ['name' => 'orders_status_purchase_date_idx']
        );
        $orders->createIndex(
            ['razorpay_order_id' => 1],
            ['name' => 'orders_razorpay_order_id_idx']
        );

        /**
         * =========================
         * PAYMENTS COLLECTION
         * =========================
         */
        $payments = $database->selectCollection('payments');

        // Already correct (sparse = ignore null)
        $payments->createIndex(
            ['razorpay_payment_id' => 1],
            [
                'name' => 'payments_razorpay_payment_id_unique',
                'unique' => true,
                'sparse' => true,
            ]
        );

        $payments->createIndex(['order_id' => 1], ['name' => 'payments_order_id_idx']);
        $payments->createIndex(['user_id' => 1], ['name' => 'payments_user_id_idx']);
        $payments->createIndex(
            ['razorpay_order_id' => 1],
            ['name' => 'payments_razorpay_order_id_idx']
        );
    }

    public function down(): void
    {
        $database = DB::connection('mongodb')->getMongoDB();

        $orders = $database->selectCollection('orders');
        $orders->dropIndex('orders_order_reference_unique');
        $orders->dropIndex('orders_user_id_idx');
        $orders->dropIndex('orders_status_purchase_date_idx');
        $orders->dropIndex('orders_razorpay_order_id_idx');

        $payments = $database->selectCollection('payments');
        $payments->dropIndex('payments_razorpay_payment_id_unique');
        $payments->dropIndex('payments_order_id_idx');
        $payments->dropIndex('payments_user_id_idx');
        $payments->dropIndex('payments_razorpay_order_id_idx');
    }
};