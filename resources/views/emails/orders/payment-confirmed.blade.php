@extends('emails.layouts.base')

@section('title', 'Payment Confirmed')

@section('content')
<div class="header">
    <h1>Payment Confirmed</h1>
</div>

<p>Hello {{ $order->user->name }},</p>

<p>Great news! We have successfully received your payment for order <strong>{{ $order->order_number }}</strong>.</p>

<div class="order-info">
    <h3>Payment Details</h3>
    <strong>Order Number:</strong> {{ $order->order_number }}<br>
    <strong>Payment Date:</strong> {{ $order->paid_at ? $order->paid_at->format('F j, Y \a\t g:i A') : now()->format('F j, Y \a\t g:i A') }}<br>
    <strong>Payment Method:</strong> {{ ucfirst($paymentMethod) }}<br>
    <strong>Amount Paid:</strong> {{ $order->formatted_total }}
</div>

<div class="order-info">
    <h3>Order Summary</h3>
    <strong>Order Status:</strong> <span class="status-badge status-success">{{ $order->status_label }}</span><br>
    <strong>Delivery Method:</strong> {{ $order->delivery_method_label }}<br>
    @if($order->isHomeDelivery() && $order->delivery_address)
        <strong>Delivery Address:</strong><br>
        @if(is_array($order->delivery_address))
            {{ $order->delivery_address['address_line_1'] ?? '' }}<br>
            @if(!empty($order->delivery_address['address_line_2']))
                {{ $order->delivery_address['address_line_2'] }}<br>
            @endif
            {{ $order->delivery_address['city'] ?? '' }}, {{ $order->delivery_address['state'] ?? '' }} {{ $order->delivery_address['postal_code'] ?? '' }}<br>
            {{ $order->delivery_address['country'] ?? '' }}
        @endif
    @endif
</div>

<div class="order-details">
    <h3>Order Items</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>{{ $item->package_name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if($order->tax_amount > 0)
            <tr>
                <td colspan="3">Subtotal</td>
                <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3">Tax ({{ $order->tax_percentage }})</td>
                <td class="text-right">${{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="3"><strong>Total Paid</strong></td>
                <td class="text-right"><strong>{{ $order->formatted_total }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="order-info">
    <h3>What's Next?</h3>
    <p>Your order is now being processed. We'll keep you updated on its progress via email notifications.</p>

    @if($order->isHomeDelivery())
        <p>Your order will be prepared and shipped to your delivery address. You'll receive tracking information once it's dispatched.</p>
    @else
        <p>Your order will be prepared for pickup at our office. You'll receive a notification when it's ready for collection.</p>
    @endif
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/orders') }}" class="button">Track Your Order</a>
</div>

<p>Thank you for your purchase! If you have any questions, please don't hesitate to contact our customer support team.</p>

<p>Best regards,<br>
The {{ config('app.name') }} Team</p>
@endsection