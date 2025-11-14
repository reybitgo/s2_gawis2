<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageReservation;
use App\Models\InventoryLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockAlert;

class InventoryManagementService
{
    /**
     * Reserve inventory for checkout (15-minute hold)
     *
     * @param Package $package
     * @param int $quantity
     * @param User $user
     * @param string $sessionId
     * @return PackageReservation|null
     */
    public function reserveInventory(Package $package, int $quantity, User $user, string $sessionId): ?PackageReservation
    {
        return DB::transaction(function () use ($package, $quantity, $user, $sessionId) {
            // Lock package for update
            $package = Package::where('id', $package->id)->lockForUpdate()->first();

            // Check available stock (accounting for active reservations)
            $availableStock = $this->getAvailableStock($package);

            if ($availableStock < $quantity) {
                Log::warning('Insufficient stock for reservation', [
                    'package_id' => $package->id,
                    'requested' => $quantity,
                    'available' => $availableStock
                ]);
                return null;
            }

            // Create reservation (15 minutes)
            $reservation = PackageReservation::create([
                'package_id' => $package->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'session_id' => $sessionId,
                'expires_at' => now()->addMinutes(15),
                'status' => 'active',
            ]);

            // Log reservation
            $this->logInventoryChange(
                $package,
                'reservation',
                $package->quantity_available,
                $package->quantity_available, // No actual change yet
                0,
                $user->id,
                "Reservation #{$reservation->id}",
                "Reserved {$quantity} units for checkout"
            );

            Log::info('Inventory reserved', [
                'reservation_id' => $reservation->id,
                'package_id' => $package->id,
                'quantity' => $quantity,
                'expires_at' => $reservation->expires_at
            ]);

            return $reservation;
        });
    }

    /**
     * Release expired or cancelled reservations
     *
     * @param PackageReservation $reservation
     * @return void
     */
    public function releaseReservation(PackageReservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $package = $reservation->package;

            // Mark as expired/cancelled
            if ($reservation->isExpired()) {
                $reservation->expire();
            } else {
                $reservation->cancel();
            }

            // Log release
            $this->logInventoryChange(
                $package,
                'release',
                $package->quantity_available,
                $package->quantity_available, // No actual change
                0,
                $reservation->user_id,
                "Reservation #{$reservation->id}",
                "Released {$reservation->quantity} units from reservation"
            );

            Log::info('Reservation released', [
                'reservation_id' => $reservation->id,
                'package_id' => $package->id,
                'quantity' => $reservation->quantity
            ]);
        });
    }

    /**
     * Complete reservation and deduct inventory (after successful payment)
     *
     * @param PackageReservation $reservation
     * @param string $orderNumber
     * @return bool
     */
    public function completeReservation(PackageReservation $reservation, string $orderNumber): bool
    {
        return DB::transaction(function () use ($reservation, $orderNumber) {
            // Lock package for update
            $package = Package::where('id', $reservation->package_id)->lockForUpdate()->first();

            if (!$package) {
                return false;
            }

            $quantityBefore = $package->quantity_available;

            // Deduct inventory
            $package->quantity_available -= $reservation->quantity;
            $package->save();

            // Mark reservation as completed
            $reservation->complete($orderNumber);

            // Log the sale
            $this->logInventoryChange(
                $package,
                'sale',
                $quantityBefore,
                $package->quantity_available,
                -$reservation->quantity,
                $reservation->user_id,
                $orderNumber,
                "Sold {$reservation->quantity} units via order {$orderNumber}"
            );

            // Check if low stock alert needed
            $this->checkLowStockAlert($package);

            Log::info('Reservation completed and inventory deducted', [
                'reservation_id' => $reservation->id,
                'package_id' => $package->id,
                'quantity' => $reservation->quantity,
                'order_number' => $orderNumber,
                'remaining_stock' => $package->quantity_available
            ]);

            return true;
        });
    }

    /**
     * Get available stock (total - active reservations)
     *
     * @param Package $package
     * @return int
     */
    public function getAvailableStock(Package $package): int
    {
        if ($package->quantity_available === null) {
            return PHP_INT_MAX; // Unlimited stock
        }

        $reservedQuantity = PackageReservation::where('package_id', $package->id)
            ->active()
            ->sum('quantity');

        return max(0, $package->quantity_available - $reservedQuantity);
    }

    /**
     * Clean up expired reservations (should run via cron)
     *
     * @return int Number of released reservations
     */
    public function cleanupExpiredReservations(): int
    {
        $expiredReservations = PackageReservation::expired()->get();

        $count = 0;
        foreach ($expiredReservations as $reservation) {
            $this->releaseReservation($reservation);
            $count++;
        }

        if ($count > 0) {
            Log::info("Cleaned up {$count} expired reservations");
        }

        return $count;
    }

    /**
     * Log inventory change
     *
     * @param Package $package
     * @param string $action
     * @param int $quantityBefore
     * @param int $quantityAfter
     * @param int $quantityChange
     * @param int|null $userId
     * @param string|null $reference
     * @param string|null $notes
     * @param array $metadata
     * @return InventoryLog
     */
    public function logInventoryChange(
        Package $package,
        string $action,
        int $quantityBefore,
        int $quantityAfter,
        int $quantityChange,
        ?int $userId = null,
        ?string $reference = null,
        ?string $notes = null,
        array $metadata = []
    ): InventoryLog {
        return InventoryLog::create([
            'package_id' => $package->id,
            'action' => $action,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'quantity_change' => $quantityChange,
            'user_id' => $userId,
            'reference' => $reference,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Restock inventory
     *
     * @param Package $package
     * @param int $quantity
     * @param User|null $user
     * @param string|null $notes
     * @return bool
     */
    public function restockInventory(Package $package, int $quantity, ?User $user = null, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($package, $quantity, $user, $notes) {
            $package = Package::where('id', $package->id)->lockForUpdate()->first();

            if (!$package || $package->quantity_available === null) {
                return false;
            }

            $quantityBefore = $package->quantity_available;
            $package->quantity_available += $quantity;
            $package->save();

            $this->logInventoryChange(
                $package,
                'restock',
                $quantityBefore,
                $package->quantity_available,
                $quantity,
                $user?->id,
                null,
                $notes ?? "Restocked {$quantity} units"
            );

            Log::info('Inventory restocked', [
                'package_id' => $package->id,
                'quantity_added' => $quantity,
                'new_total' => $package->quantity_available
            ]);

            return true;
        });
    }

    /**
     * Adjust inventory (for corrections, damages, etc.)
     *
     * @param Package $package
     * @param int $newQuantity
     * @param User|null $user
     * @param string|null $reason
     * @return bool
     */
    public function adjustInventory(Package $package, int $newQuantity, ?User $user = null, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($package, $newQuantity, $user, $reason) {
            $package = Package::where('id', $package->id)->lockForUpdate()->first();

            if (!$package || $package->quantity_available === null) {
                return false;
            }

            $quantityBefore = $package->quantity_available;
            $quantityChange = $newQuantity - $quantityBefore;

            $package->quantity_available = $newQuantity;
            $package->save();

            $this->logInventoryChange(
                $package,
                'adjustment',
                $quantityBefore,
                $newQuantity,
                $quantityChange,
                $user?->id,
                null,
                $reason ?? "Inventory adjusted from {$quantityBefore} to {$newQuantity}"
            );

            // Check low stock after adjustment
            $this->checkLowStockAlert($package);

            Log::info('Inventory adjusted', [
                'package_id' => $package->id,
                'old_quantity' => $quantityBefore,
                'new_quantity' => $newQuantity,
                'reason' => $reason
            ]);

            return true;
        });
    }

    /**
     * Check and send low stock alert if needed
     *
     * @param Package $package
     * @return void
     */
    private function checkLowStockAlert(Package $package): void
    {
        // Get low stock threshold from settings (default: 10)
        $threshold = (int) \App\Models\SystemSetting::get('low_stock_threshold', 10);

        if ($package->quantity_available !== null && $package->quantity_available <= $threshold) {
            // Check if alert was already sent recently (last 24 hours)
            $cacheKey = "low_stock_alert_{$package->id}";
            if (Cache::has($cacheKey)) {
                return;
            }

            // Send alert to admins with verified emails only
            $admins = User::role('admin')->get();
            $sentCount = 0;
            $skippedCount = 0;

            foreach ($admins as $admin) {
                try {
                    // Check if admin has verified email
                    if ($admin->hasVerifiedEmail()) {
                        Mail::to($admin->email)->send(new LowStockAlert($package));
                        $sentCount++;

                        Log::info('Low stock alert sent to admin', [
                            'package_id' => $package->id,
                            'package_name' => $package->name,
                            'admin_id' => $admin->id,
                            'admin_email' => $admin->email,
                            'current_stock' => $package->quantity_available,
                            'threshold' => $threshold
                        ]);
                    } else {
                        $skippedCount++;

                        Log::warning('Low stock alert skipped - Admin email not verified', [
                            'package_id' => $package->id,
                            'package_name' => $package->name,
                            'admin_id' => $admin->id,
                            'admin_email' => $admin->email ?? 'N/A',
                            'current_stock' => $package->quantity_available,
                            'threshold' => $threshold
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send low stock alert', [
                        'package_id' => $package->id,
                        'admin_email' => $admin->email ?? 'N/A',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Cache alert to prevent spam (24 hours)
            Cache::put($cacheKey, true, now()->addDay());

            Log::warning('Low stock alert process completed', [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'current_stock' => $package->quantity_available,
                'threshold' => $threshold,
                'emails_sent' => $sentCount,
                'emails_skipped' => $skippedCount
            ]);
        }
    }

    /**
     * Get inventory statistics for a package
     *
     * @param Package $package
     * @param int $days
     * @return array
     */
    public function getInventoryStats(Package $package, int $days = 30): array
    {
        $logs = InventoryLog::where('package_id', $package->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'asc')
            ->get();

        $salesCount = $logs->where('action', 'sale')->sum('quantity_change');
        $restockCount = $logs->where('action', 'restock')->sum('quantity_change');
        $adjustmentCount = $logs->where('action', 'adjustment')->sum('quantity_change');

        return [
            'package_id' => $package->id,
            'current_stock' => $package->quantity_available,
            'available_stock' => $this->getAvailableStock($package),
            'reserved_stock' => PackageReservation::where('package_id', $package->id)->active()->sum('quantity'),
            'period_days' => $days,
            'sales_quantity' => abs($salesCount),
            'restock_quantity' => $restockCount,
            'adjustment_quantity' => $adjustmentCount,
            'turnover_rate' => $package->quantity_available > 0
                ? round((abs($salesCount) / $package->quantity_available) * 100, 2)
                : 0,
            'avg_daily_sales' => $days > 0 ? round(abs($salesCount) / $days, 2) : 0,
            'days_until_stockout' => ($this->getAvailableStock($package) > 0 && abs($salesCount) > 0)
                ? round(($this->getAvailableStock($package) / (abs($salesCount) / $days)), 0)
                : null,
        ];
    }
}