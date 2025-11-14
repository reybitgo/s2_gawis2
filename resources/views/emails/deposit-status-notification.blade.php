<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Request {{ ucfirst($status) }}</title>
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
        <h1>Deposit Request {{ ucfirst($status) }}</h1>
        <p>Your deposit request has been {{ $status }} by our admin team</p>
    </div>

    <div class="content">
        <div class="alert">
            @if($status === 'approved')
                <strong>🎉 Great News!</strong> Your deposit has been approved and the funds have been added to your wallet. You can now use your balance for transactions.
            @else
                <strong>❗ Important:</strong> Your deposit request has been rejected. Please review the details below and contact support if you have any questions.
            @endif
        </div>

        <div class="amount">
            @if($status === 'approved')
                +${{ number_format($transaction->amount, 2) }}
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
                <li>Your wallet balance has been updated with ${{ number_format($transaction->amount, 2) }}</li>
                <li>You can now make transfers, withdrawals, and other transactions</li>
                <li>Check your dashboard to view your updated balance</li>
                <li>View your transaction history for complete records</li>
            </ul>
        </div>
        @else
        <div class="alert">
            <strong>💡 What Can You Do?</strong>
            <ul>
                <li>Contact our support team if you believe this was an error</li>
                <li>Review the admin notes above for specific reasons</li>
                <li>Submit a new deposit request with correct information</li>
                <li>Ensure your payment method details are accurate</li>
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
                <a href="{{ route('wallet.deposit') }}" class="btn">
                    💰 New Deposit
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
            <p><strong>Congratulations!</strong> Your funds are now available in your wallet.</p>
        @else
            <p><strong>Need Help?</strong> Contact our support team for assistance with your deposit.</p>
        @endif
        <p>Thank you for using our e-wallet service!</p>
    </div>
</body>
</html>