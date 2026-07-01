<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function razorpay(Request $request)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $secret = (string) env('RAZORPAY_WEBHOOK_SECRET', env('RAZORPAY_KEY_SECRET'));

        if (empty($secret)) {
            Log::error('payment.webhook.misconfigured_secret');
            return ApiResponse::error('Webhook secret missing', [], 500);
        }

        $generatedSignature = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($generatedSignature, $signature)) {
            Log::warning('payment.webhook.invalid_signature', ['signature' => $signature]);
            return ApiResponse::error('Invalid webhook signature', [], 400);
        }

        $data = $request->json()->all();
        $event = (string) ($data['event'] ?? '');
        $entity = $data['payload']['payment']['entity'] ?? [];

        $razorpayPaymentId = (string) ($entity['id'] ?? '');
        $razorpayOrderId = (string) ($entity['order_id'] ?? '');

        if ($razorpayPaymentId === '' || $razorpayOrderId === '') {
            return ApiResponse::error('Invalid webhook payload', ['payment' => ['Missing payment/order IDs']], 422);
        }

        $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();
        if (!$payment) {
            Log::warning('payment.webhook.payment_not_found', [
                'event' => $event,
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);
            return ApiResponse::success(['processed' => false], 'Payment record not found');
        }

        if (!empty($payment->razorpay_payment_id) && $payment->razorpay_payment_id === $razorpayPaymentId && in_array((string) $payment->status, ['captured', 'failed'], true)) {
            return ApiResponse::success(['processed' => false], 'Duplicate webhook ignored');
        }

        $order = Order::find($payment->order_id);
        if (!$order) {
            Log::error('payment.webhook.order_not_found', [
                'payment_id' => (string) $payment->id,
                'order_id' => (string) $payment->order_id,
            ]);
            return ApiResponse::error('Order not found for payment', [], 404);
        }

        $eventAmount = (int) ($entity['amount'] ?? 0);
        $dbAmount = (int) round(((float) $order->total) * 100);
        if ($eventAmount > 0 && $eventAmount !== $dbAmount) {
            Log::error('payment.webhook.amount_mismatch', [
                'order_id' => (string) $order->id,
                'db_amount_in_paise' => $dbAmount,
                'event_amount_in_paise' => $eventAmount,
            ]);
            return ApiResponse::error('Webhook amount mismatch', [], 422);
        }

        if ($event === 'payment.captured') {
            if ($order->canTransitionTo(Order::STATUS_PAID)) {
                $order->update([
                    'status' => Order::STATUS_PAID,
                    'razorpay_payment_id' => $razorpayPaymentId,
                ]);
            }

            $payment->update([
                'status' => 'captured',
                'razorpay_payment_id' => $razorpayPaymentId,
                'raw_payload' => ['webhook' => $data],
            ]);
        } elseif ($event === 'payment.failed') {
            if ($order->canTransitionTo(Order::STATUS_FAILED)) {
                $order->update(['status' => Order::STATUS_FAILED]);
            }

            $payment->update([
                'status' => 'failed',
                'razorpay_payment_id' => $razorpayPaymentId,
                'raw_payload' => ['webhook' => $data],
            ]);
        } else {
            return ApiResponse::success(['processed' => false], 'Event ignored');
        }

        Log::info('payment.webhook.processed', [
            'event' => $event,
            'order_id' => (string) $order->id,
            'payment_id' => (string) $payment->id,
            'razorpay_payment_id' => $razorpayPaymentId,
        ]);

        return ApiResponse::success(['processed' => true], 'Webhook processed');
    }
}

