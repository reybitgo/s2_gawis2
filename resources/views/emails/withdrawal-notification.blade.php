<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Withdrawal Request</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
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
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 10px 5px;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #ffc107;
            color: #212529;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .payment-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            color: #004085;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💸 New Withdrawal Request</h1>
        <p>A user has submitted a withdrawal request that needs your approval</p>
    </div>

    <div class="content">
        <div class="alert">
            <strong>⚡ Action Required:</strong> A user is requesting to withdraw funds from their e-wallet. Please review the request details and process the withdrawal accordingly.
        </div>

        <div class="amount">
            -${{ number_format($transaction->amount, 2) }}
        </div>

        <table class="details-table">
            <tr>
                <th>User</th>
                <td>{{ $user->name }} ({{ $user->email }})</td>
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
                <td><span class="status-badge">{{ ucfirst($transaction->status) }}</span></td>
            </tr>
            <tr>
                <th>Reference Number</th>
                <td>{{ $transaction->reference_number ?: 'Not provided' }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $transaction->description ?: 'No description provided' }}</td>
            </tr>
            <tr>
                <th>Submitted At</th>
                <td>{{ $transaction->created_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
        </table>

        @if($transaction->metadata)
            <div class="payment-info">
                <h6><strong>💳 Payment Method Details:</strong></h6>
                @if($transaction->payment_method === 'Gcash' && isset($transaction->metadata['gcash_number']))
                    <p><strong>Gcash Number:</strong> {{ $transaction->metadata['gcash_number'] }}</p>
                    <p><em>Process the withdrawal by sending funds to this Gcash number.</em></p>
                @elseif($transaction->payment_method === 'Maya' && isset($transaction->metadata['maya_number']))
                    <p><strong>Maya Number:</strong> {{ $transaction->metadata['maya_number'] }}</p>
                    <p><em>Process the withdrawal by sending funds to this Maya number.</em></p>
                @elseif($transaction->payment_method === 'Cash' && isset($transaction->metadata['pickup_location']))
                    <p><strong>Pickup Location:</strong> {{ $transaction->metadata['pickup_location'] }}</p>
                    <p><em>Arrange cash pickup at the specified location.</em></p>
                @elseif($transaction->payment_method === 'Others' && isset($transaction->metadata['payment_details']))
                    <p><strong>Payment Details:</strong></p>
                    <p style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;">{{ $transaction->metadata['payment_details'] }}</p>
                @endif
            </div>
        @endif

        <div class="alert">
            <strong>💡 Next Steps:</strong>
            <ol>
                @if($transaction->payment_method === 'Gcash' || $transaction->payment_method === 'Maya')
                    <li>Send ${{ number_format($transaction->amount, 2) }} to the {{ $transaction->payment_method }} number above</li>
                    <li>Take a screenshot or note the transaction reference</li>
                    <li>Click "Approve" and add the reference in your notes</li>
                @elseif($transaction->payment_method === 'Cash')
                    <li>Arrange cash pickup at the specified location</li>
                    <li>Coordinate with the user for pickup timing</li>
                    <li>Click "Approve" once cash is ready for pickup</li>
                @else
                    <li>Review the payment details provided by the user</li>
                    <li>Process the withdrawal according to their specified method</li>
                    <li>Click "Approve" once withdrawal is processed</li>
                @endif
                <li>Or click "Reject" if there are any issues with the request</li>
            </ol>
        </div>

        <div class="action-buttons">
            <a href="{{ route('admin.transaction.approval') }}" class="btn btn-success">
                ✅ Review & Process
            </a>
            <a href="{{ route('admin.transaction.approval') }}" class="btn">
                📋 View All Pending
            </a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from your e-wallet system.</p>
        <p>You received this email because you are an administrator.</p>
        <p><strong>Important:</strong> Always verify user payment details before processing withdrawals.</p>
    </div>
</body>
</html>