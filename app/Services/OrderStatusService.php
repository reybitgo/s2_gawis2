<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Mail\OrderStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrderStatusService
{
    /**
     * Validate if a status transition is allowed
     */
    public function isTransitionAllowed(Order $order, string $newStatus): bool
    {
        $allowedStatuses = $order->getAllowedNextStatuses();
        return in_array($newStatus, $allowedStatuses);
    }

    /**
     * Update order status with validation and history tracking
     */
    public function updateStatus(
        Order $order,
        string $newStatus,
        ?string $notes = null,
        ?string $changedBy = null,
        array $metadata = [],
        bool $notifyCustomer = true
    ): bool {
        // Auto-detect who made the change
        if (!$changedBy) {
            $changedBy = Auth::check() ? Auth::id() : 'system';
        }

        // Validate transition
        if (!$this->isTransitionAllowed($order, $newStatus)) {
            throw new \InvalidArgumentException(
                "Invalid status transition from '{$order->status}' to '{$newStatus}'"
            );
        }

        $oldStatus = $order->status;

        try {
            // Update the order status
            $order->update(['status' => $newStatus]);

            // Create status history record
            $order->statusHistory()->create([
                'status' => $newStatus,
                'notes' => $notes,
                'changed_by' => $changedBy,
                'metadata' => array_merge([
                    'previous_status' => $oldStatus,
                    'timestamp' => now()->toISOString(),
                ], $metadata)
            ]);

            // Trigger any automatic actions based on status change
            $this->triggerStatusActions($order, $newStatus, $oldStatus);

            // Send email notification to customer if requested
            if ($notifyCustomer) {
                $this->sendStatusChangeNotification($order, $oldStatus, $newStatus, $notes, $changedBy);
            }

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy,
                'notes' => $notes
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Bulk update status for multiple orders
     */
    public function bulkUpdateStatus(
        array $orderIds,
        string $newStatus,
        ?string $notes = null,
        ?string $changedBy = null,
        bool $notifyCustomer = false
    ): array {
        $results = [];
        $changedBy = $changedBy ?: (Auth::check() ? Auth::id() : 'system');

        foreach ($orderIds as $orderId) {
            try {
                $order = Order::findOrFail($orderId);
                $this->updateStatus($order, $newStatus, $notes, $changedBy, [], $notifyCustomer);
                $results['success'][] = $orderId;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Get status statistics for admin dashboard
     */
    public function getStatusStatistics(): array
    {
        $statusGroups = Order::getStatusGroups();
        $stats = [];

        foreach ($statusGroups as $groupName => $statuses) {
            $count = Order::whereIn('status', $statuses)->count();
            $stats[$groupName] = [
                'count' => $count,
                'statuses' => $statuses,
                'label' => ucfirst(str_replace('_', ' ', $groupName))
            ];
        }

        return $stats;
    }

    /**
     * Get orders requiring attention (stuck in status too long)
     */
    public function getOrdersRequiringAttention(int $hoursThreshold = 24): array
    {
        $thresholdTime = now()->subHours($hoursThreshold);

        $attentionStatuses = [
            Order::STATUS_ON_HOLD,
            Order::STATUS_DELIVERY_FAILED,
            Order::STATUS_PAYMENT_FAILED
        ];

        return Order::whereIn('status', $attentionStatuses)
            ->where('updated_at', '<', $thresholdTime)
            ->with(['user', 'orderItems'])
            ->get()
            ->toArray();
    }

    /**
     * Get status progression summary for an order
     */
    public function getOrderStatusSummary(Order $order): array
    {
        $progression = $order->getStatusProgression();
        $statusHistory = $order->statusHistory()
            ->orderBy('created_at', 'asc')
            ->get()
            ->keyBy('status');

        $summary = [];
        foreach ($progression as $status) {
            $history = $statusHistory->get($status);
            $summary[] = [
                'status' => $status,
                'label' => Order::getStatusLabels()[$status] ?? $status,
                'completed' => $order->hasReachedStatus($status),
                'is_current' => $order->status === $status,
                'timestamp' => $history ? $history->created_at : null,
                'notes' => $history ? $history->notes : null,
                'changed_by' => $history ? $history->changed_by_description : null,
                'history_id' => $history ? $history->id : null
            ];
        }

        return $summary;
    }

    /**
     * Update tracking information for shipped orders
     */
    public function updateTrackingInfo(
        Order $order,
        string $trackingNumber,
        string $courierName,
        ?\DateTime $estimatedDelivery = null
    ): bool {
        if (!$order->isHomeDelivery()) {
            throw new \InvalidArgumentException('Tracking info can only be added to home delivery orders');
        }

        $order->update([
            'tracking_number' => $trackingNumber,
            'courier_name' => $courierName,
            'estimated_delivery' => $estimatedDelivery
        ]);

        // Add to status history
        $order->statusHistory()->create([
            'status' => $order->status,
            'notes' => "Tracking information added: {$trackingNumber} via {$courierName}",
            'changed_by' => Auth::check() ? Auth::id() : 'system',
            'metadata' => [
                'action' => 'tracking_updated',
                'tracking_number' => $trackingNumber,
                'courier_name' => $courierName,
                'estimated_delivery' => $estimatedDelivery?->toISOString()
            ]
        ]);

        return true;
    }

    /**
     * Update pickup information for office pickup orders
     */
    public function updatePickupInfo(
        Order $order,
        string $pickupLocation,
        ?\DateTime $pickupDate = null,
        ?string $pickupInstructions = null
    ): bool {
        if (!$order->isOfficePickup()) {
            throw new \InvalidArgumentException('Pickup info can only be added to office pickup orders');
        }

        $order->update([
            'pickup_location' => $pickupLocation,
            'pickup_date' => $pickupDate,
            'pickup_instructions' => $pickupInstructions
        ]);

        // Add to status history
        $order->statusHistory()->create([
            'status' => $order->status,
            'notes' => "Pickup information updated: {$pickupLocation}",
            'changed_by' => Auth::check() ? Auth::id() : 'system',
            'metadata' => [
                'action' => 'pickup_updated',
                'pickup_location' => $pickupLocation,
                'pickup_date' => $pickupDate?->toISOString(),
                'pickup_instructions' => $pickupInstructions
            ]
        ]);

        return true;
    }

    /**
     * Trigger automatic actions based on status changes
     */
    private function triggerStatusActions(Order $order, string $newStatus, string $oldStatus): void
    {
        switch ($newStatus) {
            case Order::STATUS_PROCESSING:
                // Send order processing notification to customer
                // TODO: Implement notification service
                break;

            case Order::STATUS_READY_FOR_PICKUP:
                // Send pickup ready notification
                // TODO: Implement notification service
                break;

            case Order::STATUS_SHIPPED:
                // Send shipping notification with tracking
                // TODO: Implement notification service
                break;

            case Order::STATUS_DELIVERED:
            case Order::STATUS_RECEIVED_IN_OFFICE:
                // Auto-progress to completed after delivery/pickup
                // We might want to add a delay or manual confirmation
                break;

            case Order::STATUS_COMPLETED:
                // Final completion actions (reviews, loyalty points, etc.)
                // TODO: Implement completion workflow
                break;

            case Order::STATUS_CANCELLED:
                // Handle cancellation (refunds, inventory restoration)
                // TODO: Integrate with existing cancellation logic
                break;
        }
    }

    /**
     * Get recommended next statuses based on business rules
     */
    public function getRecommendedNextStatuses(Order $order): array
    {
        $allowedStatuses = $order->getAllowedNextStatuses();

        // Filter out inappropriate statuses for admin interface
        $allowedStatuses = $this->filterStatusesForAdmin($order, $allowedStatuses);

        $recommendations = [];

        foreach ($allowedStatuses as $status) {
            $recommendations[] = [
                'status' => $status,
                'label' => Order::getStatusLabels()[$status] ?? $status,
                'is_recommended' => $this->isRecommendedTransition($order, $status),
                'description' => $this->getStatusDescription($order, $status)
            ];
        }

        return $recommendations;
    }

    /**
     * Filter statuses for admin interface (remove inappropriate admin actions)
     */
    private function filterStatusesForAdmin(Order $order, array $statuses): array
    {
        // Check if this order was paid with e-wallet
        $paymentMethod = $order->metadata['payment']['method'] ?? null;

        // For e-wallet orders, hide payment-related statuses since payment is instant
        if ($paymentMethod === 'wallet') {
            $paymentStatuses = [
                Order::STATUS_PENDING,        // Pending Payment
                Order::STATUS_PAID,           // Payment Received
                Order::STATUS_PAYMENT_FAILED  // Payment Failed
            ];

            // Remove payment statuses from allowed list
            $statuses = array_filter($statuses, function($status) use ($paymentStatuses) {
                return !in_array($status, $paymentStatuses);
            });
        }

        // Remove admin-inappropriate statuses
        $adminBlockedStatuses = [
            Order::STATUS_CANCELLED,  // Only customers should cancel orders (involves refunds, point reversals)
        ];

        // Remove blocked statuses from admin interface
        $statuses = array_filter($statuses, function($status) use ($adminBlockedStatuses) {
            return !in_array($status, $adminBlockedStatuses);
        });

        return array_values($statuses);
    }

    /**
     * Check if a status transition is recommended
     */
    private function isRecommendedTransition(Order $order, string $status): bool
    {
        // Business logic for recommended transitions
        switch ($order->status) {
            case Order::STATUS_PAID:
                return $status === Order::STATUS_PROCESSING;

            case Order::STATUS_PROCESSING:
                return $status === Order::STATUS_CONFIRMED;

            case Order::STATUS_CONFIRMED:
                return $status === Order::STATUS_PACKING;

            case Order::STATUS_PACKING:
                return $order->isOfficePickup()
                    ? $status === Order::STATUS_READY_FOR_PICKUP
                    : $status === Order::STATUS_READY_TO_SHIP;

            default:
                return false;
        }
    }

    /**
     * Get description for status transition
     */
    private function getStatusDescription(Order $order, string $status): string
    {
        $descriptions = [
            Order::STATUS_PROCESSING => 'Begin processing this order',
            Order::STATUS_CONFIRMED => 'Confirm order details and availability',
            Order::STATUS_PACKING => 'Start packing items for delivery/pickup',
            Order::STATUS_READY_FOR_PICKUP => 'Mark as ready for customer pickup',
            Order::STATUS_READY_TO_SHIP => 'Mark as ready for courier pickup',
            Order::STATUS_PICKUP_NOTIFIED => 'Notify customer that order is ready',
            Order::STATUS_SHIPPED => 'Hand over to courier for delivery',
            Order::STATUS_RECEIVED_IN_OFFICE => 'Customer has collected the order',
            Order::STATUS_DELIVERED => 'Order successfully delivered to customer',
            Order::STATUS_COMPLETED => 'Mark order as fully completed',
            Order::STATUS_ON_HOLD => 'Put order on hold (use when there are issues that need resolution)',
        ];

        return $descriptions[$status] ?? 'Change order status';
    }

    /**
     * Send email notification for status change
     */
    private function sendStatusChangeNotification(Order $order, string $oldStatus, string $newStatus, ?string $notes, string $changedBy): void
    {
        try {
            // Load the user relationship if not already loaded
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }

            $user = $order->user;

            // Check if user has verified email
            if ($user->hasVerifiedEmail()) {
                // Send notification to customer
                Mail::to($user->email)->send(
                    new OrderStatusChanged($order, $oldStatus, $newStatus, $notes, $changedBy)
                );

                Log::info('Order status change notification sent', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'recipient' => $user->email,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
            } else {
                // User email not verified - skip sending and log
                Log::warning('Order status change notification skipped - User email not verified', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $user->id,
                    'user_name' => $user->fullname ?? $user->username,
                    'user_email' => $user->email ?? 'N/A',
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);

                // Notify admins about this
                $this->notifyAdminsAboutUnverifiedUser($order, $user, $newStatus);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send order status change notification', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient' => $order->user->email ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify admins about order activity from unverified user
     */
    private function notifyAdminsAboutUnverifiedUser(Order $order, $user, string $newStatus): void
    {
        try {
            $admins = \App\Models\User::role('admin')->get();

            foreach ($admins as $admin) {
                if ($admin->hasVerifiedEmail()) {
                    // Send email to admin
                    Mail::to($admin->email)->send(
                        new \App\Mail\UnverifiedUserOrderNotification($order, $user, $newStatus)
                    );

                    Log::info('Admin notified about unverified user order activity', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'unverified_user_id' => $user->id,
                        'unverified_user_name' => $user->fullname ?? $user->username,
                        'new_status' => $newStatus
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about unverified user', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}