@extends('emails.layout')

@section('content')
  <h2 style="margin:0 0 12px;color:#222;">Order Confirmed</h2>
  <p style="margin:0 0 10px;color:#555;">Your order <strong>{{ $order->order_id }}</strong> has been paid successfully.</p>
  <p style="margin:0 0 16px;color:#555;">Amount Paid: <strong>INR {{ number_format((float)$order->total, 2) }}</strong></p>

  <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:0 0 16px;">
    <thead>
      <tr>
        <th align="left" style="padding:8px;border-bottom:1px solid #e5e5e5;">Item</th>
        <th align="right" style="padding:8px;border-bottom:1px solid #e5e5e5;">Qty</th>
        <th align="right" style="padding:8px;border-bottom:1px solid #e5e5e5;">Price</th>
      </tr>
    </thead>
    <tbody>
      @foreach(($order->items ?? []) as $item)
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f0f0f0;">{{ $item['product_id'] ?? 'Product' }}</td>
          <td align="right" style="padding:8px;border-bottom:1px solid #f0f0f0;">{{ $item['quantity'] ?? 1 }}</td>
          <td align="right" style="padding:8px;border-bottom:1px solid #f0f0f0;">INR {{ number_format((float)($item['price'] ?? 0), 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <p style="margin:0 0 24px;">
    <a href="{{ env('FRONTEND_URL', '#') }}/orders/{{ $order->id }}" style="display:inline-block;background:#7EA35D;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;">View Order</a>
  </p>
  <p style="margin:0;color:#777;font-size:13px;">Invoice PDF is attached to this email.</p>
@endsection

