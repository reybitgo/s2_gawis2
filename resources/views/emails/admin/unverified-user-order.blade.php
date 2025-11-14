<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unverified User Order Activity</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ffc107;
            color: #000;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-box {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-row {
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #000;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>⚠️ Unverified User Order Activity</h2>
    </div>

    <div class="content">
        <div class="alert">
            <strong>Note:</strong> A user with an unverified email address has activity on their order. The status change notification was not sent to the user.
        </div>

        <div class="info-box">
            <h3>User Information</h3>
            <div class="info-row">
                <span class="label">User Name:</span>
                <span class="value">{{ $user->fullname ?? $user->username }}</span>
            </div>
            <div class="info-row">
                <span class="label">User ID:</span>
                <span class="value">#{{ $user->id }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $user->email ?? 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email Status:</span>
                <span class="value" style="color: #dc3545;">❌ Not Verified</span>
            </div>
        </div>

        <div class="info-box">
            <h3>Order Information</h3>
            <div class="info-row">
                <span class="label">Order Number:</span>
                <span class="value">{{ $order->order_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">New Status:</span>
                <span class="value">{{ ucfirst(str_replace('_', ' ', $newStatus)) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Order Total:</span>
                <span class="value">${{ number_format($order->total_amount, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Order Date:</span>
                <span class="value">{{ $order->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn">View Order Details</a>
        </div>

        <div class="alert">
            <strong>Action Required:</strong> You may want to contact this user through alternative means to inform them about their order status change, or encourage them to verify their email address for future notifications.
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from {{ config('app.name') }}</p>
        <p>You received this email because you are an administrator.</p>
    </div>
</body>
</html>
