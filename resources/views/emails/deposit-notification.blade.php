<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Deposit Request</title>
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
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
            color: #28a745;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 New Deposit Request</h1>
        <p>A user has submitted a deposit request that needs your approval</p>
    </div>

    <div class="content">
        <div class="alert">
            <strong>⚡ Action Required:</strong> A user has likely transferred money to your Gcash/Maya account. Please verify the transaction and approve the deposit request.
        </div>

        <div class="amount">
            ${{ number_format($transaction->amount, 2) }}
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
            @if($transaction->metadata && isset($transaction->metadata['receipt_url']))
            <tr>
                <th>Receipt</th>
                <td><a href="{{ $transaction->metadata['receipt_url'] }}" target="_blank">View Receipt</a></td>
            </tr>
            @endif
        </table>

        @if($transaction->payment_method === 'Gcash' || $transaction->payment_method === 'Maya')
        <div class="alert">
            <strong>💡 Next Steps:</strong>
            <ol>
                <li>Check your {{ $transaction->payment_method }} account for the incoming transfer</li>
                <li>Verify the amount matches: ${{ number_format($transaction->amount, 2) }}</li>
                <li>Look for sender: {{ $user->name }} or reference: {{ $transaction->reference_number ?: 'No reference' }}</li>
                <li>Click the buttons below to approve or reject the request</li>
            </ol>
        </div>
        @endif

        <div class="action-buttons">
            <a href="{{ route('admin.transaction.approval') }}" class="btn btn-success">
                ✅ Review & Approve
            </a>
            <a href="{{ route('admin.transaction.approval') }}" class="btn">
                📋 View All Pending
            </a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from your e-wallet system.</p>
        <p>You received this email because you are an administrator.</p>
        <p><strong>Important:</strong> Always verify the actual money transfer before approving deposits.</p>
    </div>
</body>
</html>