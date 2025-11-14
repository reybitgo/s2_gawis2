<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-info h1 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            margin: 0;
            color: #666;
            font-size: 1.8em;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .detail-section h3 {
            margin: 0 0 15px 0;
            color: #007bff;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #666;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals-section {
            margin-left: auto;
            width: 300px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .total-line.final {
            border-bottom: 2px solid #007bff;
            font-weight: bold;
            font-size: 1.2em;
            color: #007bff;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 30px 0;
        }
        .payment-info h3 {
            margin: 0 0 15px 0;
            color: #007bff;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .footer-note {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }
        @media print {
            body { padding: 0; }
            .invoice-container { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>{{ config('app.name', 'Gawis iHerbal') }}</h1>
                <p>
                    E-commerce Platform<br>
                    Digital Products & Services<br>
                    support@gawis.com
                </p>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <p>
                    <strong>Invoice #:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->paid_at->format('M d, Y') }}<br>
                    <strong>Status:</strong> <span class="status-badge status-paid">PAID</span>
                </p>
            </div>
        </div>

        <!-- Order Details -->
        <div class="invoice-details">
            <div class="detail-section">
                <h3>Bill To:</h3>
                <div class="detail-item">
                    <span class="detail-label">Customer:</span>
                    <span>{{ $order->user->fullname ?? $order->user->username }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span>{{ $order->user->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Customer ID:</span>
                    <span>#{{ str_pad($order->user->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>

            <div class="detail-section">
                <h3>Order Information:</h3>
                <div class="detail-item">
                    <span class="detail-label">Order Number:</span>
                    <span>{{ $order->order_number }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Order Date:</span>
                    <span>{{ $order->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Date:</span>
                    <span>{{ $order->paid_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Payment Method:</span>
                    <span>{{ ucfirst($order->metadata['payment']['method'] ?? 'E-Wallet') }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Points</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->package_name }}</strong>
                        @if($item->package_description)
                            <br><small style="color: #666;">{{ Str::limit($item->package_description, 100) }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->formatted_unit_price }}</td>
                    <td class="text-right">{{ number_format($item->total_points_awarded) }}</td>
                    <td class="text-right">{{ $item->formatted_total_price }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>{{ $order->formatted_subtotal }}</span>
            </div>
            @if($order->tax_amount > 0)
            <div class="total-line">
                <span>Tax ({{ $order->tax_percentage }}):</span>
                <span>{{ $order->formatted_tax_amount }}</span>
            </div>
            @endif
            <div class="total-line final">
                <span>Total:</span>
                <span>{{ $order->formatted_total }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        @if(isset($order->metadata['payment']))
        <div class="payment-info">
            <h3>Payment Information</h3>
            <div class="detail-item">
                <span class="detail-label">Transaction ID:</span>
                <span>{{ $order->metadata['payment']['transaction_reference'] ?? 'N/A' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Payment Method:</span>
                <span>{{ ucfirst($order->metadata['payment']['method'] ?? 'E-Wallet') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Amount Paid:</span>
                <span>{{ currency($order->metadata['payment']['amount_paid'] ?? $order->total_amount) }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Payment Status:</span>
                <span class="status-badge status-paid">PAID</span>
            </div>
        </div>
        @endif

        <!-- Points Summary -->
        @if($order->points_awarded > 0)
        <div class="payment-info">
            <h3>Loyalty Points</h3>
            <div class="detail-item">
                <span class="detail-label">Points Earned:</span>
                <span><strong>{{ number_format($order->points_awarded) }} points</strong></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Points Status:</span>
                <span>
                    @if($order->points_credited)
                        <span class="status-badge status-paid">CREDITED</span>
                    @else
                        <span class="status-badge" style="background-color: #fff3cd; color: #856404;">PENDING</span>
                    @endif
                </span>
            </div>
        </div>
        @endif

        <!-- Customer Notes -->
        @if($order->customer_notes)
        <div class="payment-info">
            <h3>Customer Notes</h3>
            <p>{{ $order->customer_notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer-note">
            <p>
                <strong>Thank you for your business!</strong><br>
                This is a computer-generated invoice. No signature required.<br>
                Generated on {{ now()->format('M d, Y \a\t g:i A') }}
            </p>
            <p style="margin-top: 20px; font-size: 0.8em;">
                For support or questions, please contact us at support@gawis.com<br>
                {{ config('app.name') }} - Digital E-commerce Platform
            </p>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>