<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Request {{ ucfirst($status) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            @if($status === 'approved')
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            @else
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            @endif
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #fff;
            padding: 30px;
            border: 1px solid #e9ecef;
            border-top: none;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border: 1px solid #e9ecef;
            border-top: none;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
            color: #6c757d;
        }
        .alert {
            @if($status === 'approved')
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            @else
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            @endif
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th,
        .details-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .details-table th {
            background: #f8f9fa;
            font-weight: 600;
            width: 40%;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            @if($status === 'approved')
            color: #28a745;
            @else
            color: #dc3545;
            @endif
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            @if($status === 'approved')
            background: #28a745;
            @else
            background: #007bff;
            @endif
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 10px 5px;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            @if($status === 'approved')
            background: #28a745;
            @else
            background: #dc3545;
            @endif
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .admin-notes {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 6px 6px 0;
        }
        .payment-info {
            @if($status === 'approved')
            background: #e7f3ff;
            border: 1px solid #b8daff;
            color: #004085;
            @else
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            @endif
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">
            @if($status === 'approved')
                ✅
            @else
                ❌
            @endif
        </div>
        <h1>Withdrawal Request {{ ucfirst($status) }}</h1>
        <p>Your withdrawal request has been {{ $status }} by our admin team</p>
    </div>

    <div class="content">
        <div class="alert">
            @if($status === 'approved')
                <strong>🎉 Great News!</strong> Your withdrawal has been approved and is being processed. You should receive your funds according to your chosen payment method soon.
            @else
                <strong>❗ Important:</strong> Your withdrawal request has been rejected. Please review the details below and contact support if you have any questions.
            @endif
        </div>

        <div class="amount">
            @if($status === 'approved')
                💸 ${{ number_format($transaction->amount, 2) }}
            @else
                ${{ number_format($transaction->amount, 2) }}
            @endif
        </div>

        <table class="details-table">
            <tr>
                <th>Transaction ID</th>
                <td>#{{ $transaction->id }}</td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>${{ number_format($transaction->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ $transaction->payment_method }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="status-badge">{{ ucfirst($status) }}</span></td>
            </tr>
            <tr>
                <th>Reference Number</th>
                <td>{{ $transaction->reference_number ?: 'Not provided' }}</td>
            </tr>
            <tr>
                <th>Submitted At</th>
                <td>{{ $transaction->created_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
            <tr>
                <th>{{ $status === 'approved' ? 'Approved' : 'Rejected' }} At</th>
                <td>{{ now()->format('F j, Y \a\t g:i A') }}</td>
            </tr>
        </table>

        @if($transaction->metadata)
            <div class="payment-info">
                <h6><strong>💳 Your Payment Method Details:</strong></h6>
                @if($transaction->payment_method === 'Gcash' && isset($transaction->metadata['gcash_number']))
                    <p><strong>Gcash Number:</strong> {{ $transaction->metadata['gcash_number'] }}</p>
                    @if($status === 'approved')
                        <p><em>Funds will be sent to this Gcash number within 1-3 business days.</em></p>
                    @endif
                @elseif($transaction->payment_method === 'Maya' && isset($transaction->metadata['maya_number']))
                    <p><strong>Maya Number:</strong> {{ $transaction->metadata['maya_number'] }}</p>
                    @if($status === 'approved')
                        <p><em>Funds will be sent to this Maya number within 1-3 business days.</em></p>
                    @endif
                @elseif($transaction->payment_method === 'Cash' && isset($transaction->metadata['pickup_location']))
                    <p><strong>Pickup Location:</strong> {{ $transaction->metadata['pickup_location'] }}</p>
                    @if($status === 'approved')
                        <p><em>Your cash will be ready for pickup at this location. We'll contact you when ready.</em></p>
                    @endif
                @elseif($transaction->payment_method === 'Others' && isset($transaction->metadata['payment_details']))
                    <p><strong>Your Payment Details:</strong></p>
                    <p style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;">{{ $transaction->metadata['payment_details'] }}</p>
                @endif
            </div>
        @endif

        @if($adminNotes)
        <div class="admin-notes">
            <h6><strong>📝 Admin Notes:</strong></h6>
            <p>{{ $adminNotes }}</p>
        </div>
        @endif

        @if($status === 'approved')
        <div class="alert">
            <strong>💡 What's Next?</strong>
            <ul>
                @if($transaction->payment_method === 'Gcash' || $transaction->payment_method === 'Maya')
                    <li>You will receive the funds in your {{ $transaction->payment_method }} account within 1-3 business days</li>
                    <li>Check your {{ $transaction->payment_method }} notifications for incoming transfers</li>
                    <li>Keep this email as proof of your withdrawal approval</li>
                @elseif($transaction->payment_method === 'Cash')
                    <li>We will contact you when your cash is ready for pickup</li>
                    <li>Bring a valid ID when collecting your cash</li>
                    <li>Pickup is available during business hours</li>
                @else
                    <li>Your withdrawal will be processed according to your specified payment method</li>
                    <li>Processing time may vary depending on your chosen method</li>
                    <li>You may receive additional instructions if needed</li>
                @endif
                <li>Contact support if you don't receive your funds within the expected timeframe</li>
            </ul>
        </div>
        @else
        <div class="alert">
            <strong>💡 What Can You Do?</strong>
            <ul>
                <li>Contact our support team if you believe this was an error</li>
                <li>Review the admin notes above for specific reasons</li>
                <li>Submit a new withdrawal request with correct information</li>
                <li>Ensure your payment method details are accurate and valid</li>
                @if($transaction->type === 'withdrawal')
                    <li>Your funds have been returned to your wallet balance</li>
                @endif
            </ul>
        </div>
        @endif

        <div class="action-buttons">
            @if($status === 'approved')
                <a href="{{ route('dashboard') }}" class="btn">
                    🏠 View Dashboard
                </a>
                <a href="{{ route('wallet.transactions') }}" class="btn">
                    📋 Transaction History
                </a>
            @else
                <a href="{{ route('wallet.withdraw') }}" class="btn">
                    💸 New Withdrawal
                </a>
                <a href="{{ route('wallet.transactions') }}" class="btn">
                    📋 Transaction History
                </a>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from your e-wallet system.</p>
        @if($status === 'approved')
            <p><strong>Thank you!</strong> Your withdrawal is being processed.</p>
        @else
            <p><strong>Need Help?</strong> Contact our support team for assistance with your withdrawal.</p>
        @endif
        <p>Thank you for using our e-wallet service!</p>
    </div>
</body>
</html>