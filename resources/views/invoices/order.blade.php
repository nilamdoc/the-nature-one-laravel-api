<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; }
    .header { margin-bottom: 20px; }
    .title { font-size: 22px; font-weight: bold; color: #7EA35D; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f7f7f7; text-align: left; }
    .text-right { text-align: right; }
  </style>
</head>
<body>
  <div class="header">
    <div class="title">NatureOne Invoice</div>
    <div>Order ID: {{ $order->order_id }}</div>
    <div>Date: {{ optional($order->purchase_date)->format('Y-m-d H:i') }}</div>
    <div>Customer: {{ $order->customer_name }}</div>
    <div>Payment ID: {{ $payment_id }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th class="text-right">Qty</th>
        <th class="text-right">Price</th>
        <th class="text-right">Total</th>
      </tr>
    </thead>
    <tbody>
      @php $grand = 0; @endphp
      @foreach(($items ?? []) as $item)
        @php
          $qty = (int)($item['quantity'] ?? 1);
          $price = (float)($item['price'] ?? 0);
          $lineTotal = $qty * $price;
          $grand += $lineTotal;
        @endphp
        <tr>
          <td>{{ $item['product_id'] ?? 'Product' }}</td>
          <td class="text-right">{{ $qty }}</td>
          <td class="text-right">INR {{ number_format($price, 2) }}</td>
          <td class="text-right">INR {{ number_format($lineTotal, 2) }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="3" class="text-right"><strong>Grand Total</strong></td>
        <td class="text-right"><strong>INR {{ number_format($grand, 2) }}</strong></td>
      </tr>
    </tbody>
  </table>
</body>
</html>

