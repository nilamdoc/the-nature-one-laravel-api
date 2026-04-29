<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Mail\OrderSuccessMail;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        try {
            $payload = $request->validate([
                'order_reference' => 'nullable|string|max:100',
                'shipping' => 'required|array',
                'shipping.firstName' => 'required|string|max:100',
                'shipping.lastName' => 'required|string|max:100',
                'shipping.email' => 'required|email',
                'shipping.phone' => 'required|string|max:20',
                'shipping.address' => 'required|string|max:500',
                'shipping.city' => 'required|string|max:120',
                'shipping.state' => 'required|string|max:120',
                'shipping.pincode' => 'required|string|max:20',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'tax' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:1',
            ]);

            $orderReference = $payload['order_reference'] ?? (string) Str::uuid();

            $existingOrder = Order::where('order_reference', $orderReference)->first();
            if ($existingOrder) {
                $existingPayment = Payment::where('order_id', (string) $existingOrder->id)->first();

                return ApiResponse::success([
                    'id' => (string) ($existingOrder->razorpay_order_id ?? ''),
                    'amount' => (int) round(((float) $existingOrder->total) * 100),
                    'currency' => 'INR',
                    'key_id' => env('RAZORPAY_KEY_ID'),
                    'internal_order_id' => (string) $existingOrder->id,
                    'order_reference' => $orderReference,
                    'payment_status' => (string) ($existingPayment->status ?? 'created'),
                ], 'Payment order reused');
            }

            $expectedTotal = collect($payload['items'])->sum(function ($item) {
                return ((float) $item['price']) * ((int) $item['quantity']);
            }) + (float) $payload['tax'];

            if (round($expectedTotal, 2) !== round((float) $payload['total'], 2)) {
                return ApiResponse::error('Amount mismatch', ['total' => ['Provided total does not match item total']], 422);
            }

            $keyId = env('RAZORPAY_KEY_ID');
            $keySecret = env('RAZORPAY_KEY_SECRET');
            if (!$keyId || !$keySecret) {
                return ApiResponse::error('Razorpay configuration missing', ['general' => ['Missing Razorpay credentials']], 500);
            }

            $api = new Api($keyId, $keySecret);
            $amountInPaise = (int) round(((float) $payload['total']) * 100);

            $rzpOrder = $api->order->create([
                'receipt' => 'rcpt_' . Str::upper(Str::random(10)),
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);

            $order = Order::create([
                'order_id' => 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
                'order_reference' => $orderReference,
                'user_id' => null,
                'email' => $payload['shipping']['email'],
                'shipping' => $payload['shipping'],
                'customer_name' => trim($payload['shipping']['firstName'] . ' ' . $payload['shipping']['lastName']),
                'purchase_date' => now(),
                'items' => $payload['items'],
                'total' => (float) $payload['total'],
                'payment' => 'razorpay',
                'razorpay_order_id' => $rzpOrder['id'],
                'status' => Order::STATUS_PENDING,
            ]);

            Payment::create([
                'order_id' => (string) $order->id,
                'user_id' => null,
                'amount' => (float) $payload['total'],
                'currency' => 'INR',
                'razorpay_order_id' => $rzpOrder['id'],
                'status' => 'created',
                'raw_payload' => ['request' => $payload, 'razorpay_order' => $rzpOrder->toArray()],
            ]);

            Log::info('payment.create_order.success', [
                'order_id' => (string) $order->id,
                'order_reference' => $orderReference,
                'razorpay_order_id' => (string) $rzpOrder['id'],
            ]);

            return ApiResponse::success([
                'id' => $rzpOrder['id'],
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'key_id' => $keyId,
                'internal_order_id' => (string) $order->id,
                'order_reference' => $orderReference,
            ], 'Payment order created');
        } catch (Throwable $e) {
            Log::error('payment.create_order.failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Unable to create payment order', ['error' => $e->getMessage()], 500);
        }
    }

    public function verify(Request $request)
    {
        try {
            $payload = $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'internal_order_id' => 'required|string',
            ]);

            $order = Order::find($payload['internal_order_id']);
            if (!$order) {
                return ApiResponse::error('Order not found', ['order' => ['Invalid order']], 404);
            }

            if ((string) $order->status === Order::STATUS_PAID) {
                return ApiResponse::success([
                    'order_id' => (string) $order->id,
                    'status' => Order::STATUS_PAID,
                ], 'Payment already verified');
            }

            $payment = Payment::where('order_id', (string) $order->id)->first();
            if (!$payment) {
                return ApiResponse::error('Payment record not found', ['payment' => ['Missing payment record']], 404);
            }

            if ((string) $order->razorpay_order_id !== $payload['razorpay_order_id']) {
                Log::warning('payment.verify.razorpay_order_mismatch', [
                    'order_id' => (string) $order->id,
                    'expected_razorpay_order_id' => (string) $order->razorpay_order_id,
                    'received_razorpay_order_id' => $payload['razorpay_order_id'],
                ]);
                return ApiResponse::error('Razorpay order mismatch', ['payment' => ['Invalid Razorpay order mapping']], 422);
            }

            if (!empty($payment->razorpay_payment_id) && $payment->razorpay_payment_id !== $payload['razorpay_payment_id']) {
                Log::warning('payment.verify.duplicate_payment_attempt', [
                    'order_id' => (string) $order->id,
                    'existing_payment_id' => (string) $payment->razorpay_payment_id,
                    'incoming_payment_id' => $payload['razorpay_payment_id'],
                ]);
                return ApiResponse::error('Payment already mapped to a different payment ID', ['payment' => ['Duplicate payment attempt']], 409);
            }

            $secret = env('RAZORPAY_KEY_SECRET');
            $keyId = env('RAZORPAY_KEY_ID');
            if (!$secret || !$keyId) {
                return ApiResponse::error('Razorpay configuration missing', ['general' => ['Missing Razorpay credentials']], 500);
            }

            $generatedSignature = hash_hmac(
                'sha256',
                $payload['razorpay_order_id'] . "|" . $payload['razorpay_payment_id'],
                (string) $secret
            );

            if (!hash_equals($generatedSignature, $payload['razorpay_signature'])) {
                Payment::where('order_id', (string) $order->id)->update([
                    'status' => 'signature_mismatch',
                    'razorpay_payment_id' => $payload['razorpay_payment_id'],
                    'razorpay_signature' => $payload['razorpay_signature'],
                ]);
                Log::warning('payment.verify.signature_mismatch', [
                    'order_id' => (string) $order->id,
                    'razorpay_order_id' => $payload['razorpay_order_id'],
                    'razorpay_payment_id' => $payload['razorpay_payment_id'],
                ]);
                return ApiResponse::error('Signature verification failed', ['payment' => ['Invalid signature']], 400);
            }

            $api = new Api($keyId, $secret);
            $razorpayPayment = $api->payment->fetch($payload['razorpay_payment_id']);
            $razorpayAmountInPaise = (int) ($razorpayPayment['amount'] ?? 0);
            $dbAmountInPaise = (int) round(((float) $order->total) * 100);

            if ($dbAmountInPaise !== $razorpayAmountInPaise) {
                Payment::where('order_id', (string) $order->id)->update([
                    'status' => 'amount_mismatch',
                    'razorpay_payment_id' => $payload['razorpay_payment_id'],
                    'razorpay_signature' => $payload['razorpay_signature'],
                    'raw_payload' => [
                        'verify' => $payload,
                        'razorpay_payment' => $razorpayPayment->toArray(),
                        'db_amount_in_paise' => $dbAmountInPaise,
                    ],
                ]);

                if ($order->canTransitionTo(Order::STATUS_FAILED)) {
                    $order->update(['status' => Order::STATUS_FAILED]);
                }

                Log::error('payment.verify.amount_mismatch', [
                    'order_id' => (string) $order->id,
                    'db_amount_in_paise' => $dbAmountInPaise,
                    'razorpay_amount_in_paise' => $razorpayAmountInPaise,
                    'razorpay_payment_id' => $payload['razorpay_payment_id'],
                ]);

                return ApiResponse::error('Amount mismatch', ['payment' => ['Payment amount does not match order amount']], 422);
            }

            if (!$order->canTransitionTo(Order::STATUS_PAID)) {
                return ApiResponse::error('Invalid order state transition', ['order' => ['Order cannot be moved to paid']], 409);
            }

            $order->update([
                'status' => Order::STATUS_PAID,
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
            ]);

            Payment::where('order_id', (string) $order->id)->update([
                'status' => 'captured',
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
                'razorpay_signature' => $payload['razorpay_signature'],
                'raw_payload' => ['verify' => $payload],
            ]);

            $invoiceData = [
                'order' => $order,
                'items' => $order->items ?? [],
                'payment_id' => $payload['razorpay_payment_id'],
            ];
            $pdf = Pdf::loadView('invoices.order', $invoiceData);

            if (!empty($order->email)) {
                Mail::to($order->email)->send(new OrderSuccessMail($order, $pdf->output()));
            }

            Log::info('payment.verify.success', [
                'order_id' => (string) $order->id,
                'razorpay_payment_id' => $payload['razorpay_payment_id'],
            ]);

            return ApiResponse::success([
                'order_id' => (string) $order->id,
                'status' => 'paid',
            ], 'Payment verified');
        } catch (Throwable $e) {
            Log::error('payment.verify.failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Payment verification failed', ['error' => $e->getMessage()], 500);
        }
    }
}
