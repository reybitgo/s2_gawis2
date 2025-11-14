@extends('emails.layouts.base')

@section('title', 'Order Cancelled')

@section('content')
<div class="header" style="background-color: #dc3545;">
    <h1>Order Cancelled</h1>
</div>

<p>Hello {{ $order->user->name }},</p>

<p>We wanted to confirm that your order <strong>{{ $order->order_number }}</strong> has been cancelled as requested.</p>

<div class="order-info">
    <h3>Cancellation Details</h3>
    <strong>Order Number:</strong> {{ $order->order_number }}<br>
    <strong>Original Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}<br>
    <strong>Cancellation Date:</strong> {{ $order->cancelled_at ? $order->cancelled_at->format('F j, Y \a\t g:i A') : now()->format('F j, Y \a\t g:i A') }}<br>
    <strong>Order Total:</strong> {{ $order->formatted_total }}

    @if($reason)
    <br><br><strong>Cancellation Reason:</strong><br>
    <em>{{ $reason }}</em>
    @endif
</div>

@if($refundProcessed)
<div class="order-info" style="background-color: #d4edda; border-left: 4px solid #28a745;">
    <h3 style="color: #155724;">Refund Processed</h3>
    <p style="color: #155724; margin: 0;">
        <strong>Good news!</strong> Your refund of {{ $order->formatted_total }} has been processed and credited back to your wallet.
        The funds should be available in your account immediately.
    </p>
</div>
@else
@if($order->isPaid())
<div class="order-info" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
    <h3 style="color: #856404;">Refund Processing</h3>
    <p style="color: #856404; margin: 0;">
        Since your order was paid, we are processing a refund of {{ $order->formatted_total }} to your wallet.
        The refund should be completed within 1-2 business days.
    </p>
</div>
@endif
@endif

<div class="order-details">
    <h3>Cancelled Order Items</h3>
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
                <td colspan="3"><strong>Total Cancelled</strong></td>
                <td class="text-right"><strong>{{ $order->formatted_total }}</strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="order-info">
    <h3>Need Help?</h3>
    <p>If you cancelled this order by mistake or have any questions about the cancellation, please contact our customer support team immediately.</p>
    <p>You can also browse our products again and place a new order anytime.</p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/packages') }}" class="button">Browse Products</a>
    <a href="{{ url('/orders') }}" class="button" style="background-color: #6c757d;">View Order History</a>
</div>

<p>We're sorry to see this order go, but we hope to serve you again in the future!</p>

<p>Best regards,<br>
The {{ config('app.name') }} Team</p>
@endsection