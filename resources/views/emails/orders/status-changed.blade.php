@extends('emails.layouts.base')

@section('title', 'Order Status Update')

@section('content')
<div class="header">
    <h1>Order Status Update</h1>
</div>

<p>Hello {{ $order->user->name }},</p>

<p>We wanted to let you know that your order status has been updated.</p>

<div class="order-info">
    <h3>Order Details</h3>
    <strong>Order Number:</strong> {{ $order->order_number }}<br>
    <strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}<br>
    <strong>Total Amount:</strong> {{ $order->formatted_total }}
</div>

<div class="order-info">
    <h3>Status Change</h3>
    <strong>Previous Status:</strong> <span class="status-badge status-secondary">{{ $oldStatusLabel }}</span><br>
    <strong>Current Status:</strong> <span class="status-badge status-primary">{{ $newStatusLabel }}</span><br>

    @if($notes)
    <br><strong>Notes:</strong><br>
    <em>{{ $notes }}</em>
    @endif
</div>

@if($order->isHomeDelivery() && in_array($newStatus, [\App\Models\Order::STATUS_SHIPPED, \App\Models\Order::STATUS_IN_TRANSIT]))
    @if($order->tracking_number)
    <div class="order-info">
        <h3>Tracking Information</h3>
        <strong>Tracking Number:</strong> {{ $order->tracking_number }}<br>
        @if($order->courier_name)
        <strong>Courier:</strong> {{ $order->courier_name }}<br>
        @endif
        @if($order->estimated_delivery)
        <strong>Estimated Delivery:</strong> {{ $order->estimated_delivery->format('F j, Y') }}<br>
        @endif
    </div>
    @endif
@endif

@if($order->isOfficePickup() && $newStatus === \App\Models\Order::STATUS_READY_FOR_PICKUP)
    @if($order->pickup_location)
    <div class="order-info">
        <h3>Pickup Information</h3>
        <strong>Pickup Location:</strong> {{ $order->pickup_location }}<br>
        @if($order->pickup_date)
        <strong>Available from:</strong> {{ $order->pickup_date->format('F j, Y') }}<br>
        @endif
        @if($order->pickup_instructions)
        <strong>Instructions:</strong> {{ $order->pickup_instructions }}<br>
        @endif
    </div>
    @endif
@endif

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
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ $order->formatted_total }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/orders') }}" class="button">View Order Details</a>
</div>

<p>If you have any questions about your order, please don't hesitate to contact our customer support team.</p>

<p>Best regards,<br>
The {{ config('app.name') }} Team</p>
@endsection