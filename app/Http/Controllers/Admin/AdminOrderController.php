<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use App\Services\InputSanitizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    protected OrderStatusService $orderStatusService;
    protected InputSanitizationService $sanitizationService;

    public function __construct(
        OrderStatusService $orderStatusService,
        InputSanitizationService $sanitizationService
    ) {
        $this->orderStatusService = $orderStatusService;
        $this->sanitizationService = $sanitizationService;
    }

    /**
     * Display order listing with filtering and search
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.package'])
            ->orderBy('created_at', 'asc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_group')) {
            $statusGroups = Order::getStatusGroups();
            if (isset($statusGroups[$request->status_group])) {
                $query->whereIn('status', $statusGroups[$request->status_group]);
            }
        }

        if ($request->filled('delivery_method')) {
            $query->where('delivery_method', $request->delivery_method);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_notes', 'like', "%{$search}%")
                  ->orWhere('admin_notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Order count statistics
        $statusStats = $this->orderStatusService->getStatusStatistics();

        // Get orders requiring attention
        $ordersRequiringAttention = $this->orderStatusService->getOrdersRequiringAttention();

        // Paginate results
        $perPage = $this->getPerPage($request, 15);
        $orders = $query->paginate($perPage)->withQueryString();

        // Add filtered statuses for each order for Quick Actions
        $orders->getCollection()->transform(function ($order) {
            $order->adminAllowedStatuses = $this->orderStatusService->getRecommendedNextStatuses($order);
            return $order;
        });

        // Get filtered status labels for bulk actions (excluding cancelled)
        $allStatuses = Order::getStatusLabels();
        $adminBlockedStatuses = [Order::STATUS_CANCELLED];
        $adminAllowedStatusLabels = array_filter($allStatuses, function($status) use ($adminBlockedStatuses) {
            return !in_array($status, $adminBlockedStatuses);
        }, ARRAY_FILTER_USE_KEY);

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'Order Management'],
        ];

        return view('admin.orders.index', compact(
            'orders',
            'statusStats',
            'ordersRequiringAttention',
            'adminAllowedStatusLabels',
            'perPage',
            'breadcrumbs'
        ));
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.package', 'statusHistory.changer']);

        // Get status progression summary
        $statusSummary = $this->orderStatusService->getOrderStatusSummary($order);

        // Get recommended next statuses
        $recommendedStatuses = $this->orderStatusService->getRecommendedNextStatuses($order);

        return view('admin.orders.show', compact(
            'order',
            'statusSummary',
            'recommendedStatuses'
        ));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'notify_customer' => 'sometimes|boolean',
            'delivered_at' => 'nullable|date'
        ]);

        try {
            // Handle checkbox value: if checked it sends true, if unchecked it's not sent at all
            $notifyCustomer = $request->has('notify_customer') && $request->input('notify_customer');

            // If status is being set to 'delivered' and delivered_at is provided, update the order
            if ($validated['status'] === Order::STATUS_DELIVERED && !empty($validated['delivered_at'])) {
                $order->update([
                    'delivered_at' => $validated['delivered_at']
                ]);
            }

            $this->orderStatusService->updateStatus(
                $order,
                $validated['status'],
                $validated['notes'] ?? null,
                Auth::id(),
                [], // metadata
                $notifyCustomer
            );

            // Note: Customer notification is sent based on notify_customer checkbox

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'new_status' => $validated['status'],
                'status_label' => Order::getStatusLabels()[$validated['status']] ?? $validated['status']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Add admin notes to order
     */
    public function addNotes(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        // Sanitize admin notes
        $sanitizedNotes = $this->sanitizationService->sanitizeNotes($validated['notes']);

        try {
            $order->update(['admin_notes' => $sanitizedNotes]);

            // Log in status history
            $order->statusHistory()->create([
                'status' => $order->status,
                'notes' => "Admin notes updated: " . $sanitizedNotes,
                'changed_by' => Auth::id(),
                'metadata' => [
                    'action' => 'notes_updated',
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notes added successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add notes'
            ], 400);
        }
    }

    /**
     * Update tracking information
     */
    public function updateTracking(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
            'courier_name' => 'required|string|max:255',
            'estimated_delivery' => 'nullable|date'
        ]);

        try {
            $estimatedDelivery = $validated['estimated_delivery']
                ? new \DateTime($validated['estimated_delivery'])
                : null;

            $this->orderStatusService->updateTrackingInfo(
                $order,
                $validated['tracking_number'],
                $validated['courier_name'],
                $estimatedDelivery
            );

            return response()->json([
                'success' => true,
                'message' => 'Tracking information updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update pickup information
     */
    public function updatePickup(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'pickup_location' => 'required|string|max:255',
            'pickup_date' => 'nullable|date',
            'pickup_instructions' => 'nullable|string|max:1000'
        ]);

        try {
            $pickupDate = $validated['pickup_date']
                ? new \DateTime($validated['pickup_date'])
                : null;

            $this->orderStatusService->updatePickupInfo(
                $order,
                $validated['pickup_location'],
                $pickupDate,
                $validated['pickup_instructions'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Pickup information updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Bulk update order statuses
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'notify_customer' => 'boolean'
        ]);

        try {
            $results = $this->orderStatusService->bulkUpdateStatus(
                $validated['order_ids'],
                $validated['status'],
                $validated['notes'] ?? null,
                Auth::id(),
                $validated['notify_customer'] ?? false
            );

            $successCount = count($results['success'] ?? []);
            $errorCount = count($results['errors'] ?? []);

            return response()->json([
                'success' => true,
                'message' => "Updated {$successCount} orders successfully" .
                           ($errorCount > 0 ? ", {$errorCount} failed" : ""),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'orderItems.package']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('delivery_method')) {
            $query->where('delivery_method', $request->delivery_method);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $orders = $query->get();

        $filename = 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Status',
                'Payment Status',
                'Delivery Method',
                'Total Amount',
                'Items Count',
                'Order Date',
                'Notes'
            ]);

            // CSV Data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->user->name,
                    $order->user->email,
                    $order->status_label,
                    $order->payment_status,
                    $order->delivery_method_label,
                    $order->formatted_total,
                    $order->getTotalItemsCount(),
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->admin_notes ?? $order->customer_notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get order analytics data
     */
    public function analytics(Request $request): JsonResponse
    {
        $statusStats = $this->orderStatusService->getStatusStatistics();
        $ordersRequiringAttention = $this->orderStatusService->getOrdersRequiringAttention();

        // Additional analytics
        $totalOrders = Order::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $todayOrders = Order::whereDate('created_at', today())->count();

        // Status distribution for charts
        $statusDistribution = [];
        foreach (Order::getStatusLabels() as $status => $label) {
            $count = Order::where('status', $status)->count();
            if ($count > 0) {
                $statusDistribution[] = [
                    'status' => $status,
                    'label' => $label,
                    'count' => $count
                ];
            }
        }

        return response()->json([
            'status_stats' => $statusStats,
            'orders_requiring_attention' => $ordersRequiringAttention,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'today_orders' => $todayOrders,
            'status_distribution' => $statusDistribution
        ]);
    }

    /**
     * Get real-time order updates
     */
    public function getUpdates(Request $request): JsonResponse
    {
        $lastUpdate = $request->get('last_update', now()->subMinutes(5));

        $recentOrders = Order::with(['user', 'orderItems.package'])
            ->where('updated_at', '>', $lastUpdate)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $pendingCount = Order::where('status', Order::STATUS_PAID)->count();
        $attentionCount = count($this->orderStatusService->getOrdersRequiringAttention(24));

        return response()->json([
            'recent_orders' => $recentOrders,
            'pending_count' => $pendingCount,
            'attention_count' => $attentionCount,
            'last_update' => now()->toISOString()
        ]);
    }

    /**
     * Update notes for a specific status history entry
     */
    public function updateTimelineNotes(Request $request, \App\Models\OrderStatusHistory $statusHistory): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $notes = $request->input('notes');

            // Log the update for debugging
            Log::info('Updating timeline notes', [
                'history_id' => $statusHistory->id,
                'old_notes' => $statusHistory->notes,
                'new_notes' => $notes,
                'old_notes_length' => strlen($statusHistory->notes ?? ''),
                'new_notes_length' => strlen($notes ?? ''),
            ]);

            $statusHistory->update([
                'notes' => $notes
            ]);

            // Refresh to get the actual saved value
            $statusHistory->refresh();

            Log::info('Timeline notes updated', [
                'history_id' => $statusHistory->id,
                'saved_notes' => $statusHistory->notes,
                'saved_notes_length' => strlen($statusHistory->notes ?? ''),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Timeline notes updated successfully',
                'notes' => $statusHistory->notes
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update timeline notes', [
                'history_id' => $statusHistory->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update timeline notes'
            ], 500);
        }
    }
}