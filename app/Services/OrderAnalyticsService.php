<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderAnalyticsService
{
    /**
     * Get comprehensive order analytics
     */
    public function getOrderAnalytics(string $period = '30days'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'overview' => $this->getOverviewMetrics($startDate),
            'revenue' => $this->getRevenueMetrics($startDate),
            'status_distribution' => $this->getStatusDistribution($startDate),
            'delivery_method_distribution' => $this->getDeliveryMethodDistribution($startDate),
            'package_performance' => $this->getPackagePerformance($startDate),
            'daily_trends' => $this->getDailyTrends($startDate),
            'fulfillment_metrics' => $this->getFulfillmentMetrics($startDate),
        ];
    }

    /**
     * Get overview metrics
     */
    private function getOverviewMetrics(Carbon $startDate): array
    {
        $totalOrders = Order::where('created_at', '>=', $startDate)->count();
        $totalRevenue = Order::where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $avgOrderValue = $totalOrders > 0
            ? Order::where('created_at', '>=', $startDate)
                ->where('payment_status', 'paid')
                ->avg('total_amount')
            : 0;

        $conversionRate = $this->calculateConversionRate($startDate);

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'avg_order_value' => $avgOrderValue,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Get revenue metrics with growth comparison
     */
    private function getRevenueMetrics(Carbon $startDate): array
    {
        $currentRevenue = Order::where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $previousPeriod = $this->getPreviousPeriod($startDate);
        $previousRevenue = Order::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $growthRate = $previousRevenue > 0
            ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
            : 0;

        return [
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'growth_rate' => $growthRate,
            'is_growth_positive' => $growthRate > 0,
        ];
    }

    /**
     * Get status distribution
     */
    private function getStatusDistribution(Carbon $startDate): array
    {
        $distribution = Order::where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => [
                    'count' => $item->count,
                    'label' => Order::getStatusLabels()[$item->status] ?? $item->status
                ]];
            })
            ->toArray();

        // Add status groups
        $statusGroups = Order::getStatusGroups();
        $groupedDistribution = [];

        foreach ($statusGroups as $groupName => $statuses) {
            $groupTotal = 0;
            foreach ($statuses as $status) {
                if (isset($distribution[$status])) {
                    $groupTotal += $distribution[$status]['count'];
                }
            }
            $groupedDistribution[$groupName] = [
                'count' => $groupTotal,
                'label' => ucfirst(str_replace('_', ' ', $groupName))
            ];
        }

        return [
            'by_status' => $distribution,
            'by_group' => $groupedDistribution,
        ];
    }

    /**
     * Get delivery method distribution
     */
    private function getDeliveryMethodDistribution(Carbon $startDate): array
    {
        return Order::where('created_at', '>=', $startDate)
            ->select('delivery_method', DB::raw('count(*) as count'))
            ->groupBy('delivery_method')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = $item->delivery_method === 'office_pickup'
                    ? 'Office Pickup'
                    : 'Home Delivery';
                return [$item->delivery_method => [
                    'count' => $item->count,
                    'label' => $label
                ]];
            })
            ->toArray();
    }

    /**
     * Get package performance metrics
     */
    private function getPackagePerformance(Carbon $startDate): array
    {
        $topPackages = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('packages', 'order_items.package_id', '=', 'packages.id')
            ->where('orders.created_at', '>=', $startDate)
            ->where('orders.payment_status', 'paid')
            ->select(
                'packages.name',
                'packages.id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('packages.id', 'packages.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        return [
            'top_packages' => $topPackages->toArray(),
            'total_packages_sold' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', $startDate)
                ->where('orders.payment_status', 'paid')
                ->sum('order_items.quantity'),
        ];
    }

    /**
     * Get daily trends
     */
    private function getDailyTrends(Carbon $startDate): array
    {
        $dailyData = Order::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as revenue'),
                DB::raw('COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_orders')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'daily_data' => $dailyData,
            'period_start' => $startDate->toDateString(),
            'period_end' => now()->toDateString(),
        ];
    }

    /**
     * Get fulfillment metrics
     */
    private function getFulfillmentMetrics(Carbon $startDate): array
    {
        $averageProcessingTime = $this->getAverageProcessingTime($startDate);
        $fulfillmentRates = $this->getFulfillmentRates($startDate);

        return [
            'avg_processing_time' => $averageProcessingTime,
            'fulfillment_rates' => $fulfillmentRates,
            'orders_requiring_attention' => Order::whereIn('status', ['on_hold', 'delivery_failed', 'payment_failed'])
                ->where('created_at', '>=', $startDate)
                ->count(),
        ];
    }

    /**
     * Calculate average processing time
     */
    private function getAverageProcessingTime(Carbon $startDate): array
    {
        $processingTimes = DB::table('order_status_histories as start_history')
            ->join('order_status_histories as end_history', 'start_history.order_id', '=', 'end_history.order_id')
            ->join('orders', 'start_history.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $startDate)
            ->where('start_history.status', 'paid')
            ->where('end_history.status', 'completed')
            ->select(
                DB::raw('TIMESTAMPDIFF(HOUR, start_history.created_at, end_history.created_at) as hours_diff')
            )
            ->get();

        $avgHours = $processingTimes->avg('hours_diff') ?? 0;

        return [
            'average_hours' => round($avgHours, 2),
            'average_days' => round($avgHours / 24, 1),
            'sample_size' => $processingTimes->count(),
        ];
    }

    /**
     * Get fulfillment rates by delivery method
     */
    private function getFulfillmentRates(Carbon $startDate): array
    {
        $totalOrders = Order::where('created_at', '>=', $startDate)
            ->where('payment_status', 'paid')
            ->count();

        $completedOrders = Order::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->count();

        $officePickupRate = $this->getDeliveryMethodFulfillmentRate($startDate, 'office_pickup');
        $homeDeliveryRate = $this->getDeliveryMethodFulfillmentRate($startDate, 'home_delivery');

        return [
            'overall_rate' => $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0,
            'office_pickup_rate' => $officePickupRate,
            'home_delivery_rate' => $homeDeliveryRate,
        ];
    }

    /**
     * Get fulfillment rate for specific delivery method
     */
    private function getDeliveryMethodFulfillmentRate(Carbon $startDate, string $deliveryMethod): float
    {
        $totalOrders = Order::where('created_at', '>=', $startDate)
            ->where('delivery_method', $deliveryMethod)
            ->where('payment_status', 'paid')
            ->count();

        if ($totalOrders === 0) {
            return 0;
        }

        $completedOrders = Order::where('created_at', '>=', $startDate)
            ->where('delivery_method', $deliveryMethod)
            ->where('status', 'completed')
            ->count();

        return ($completedOrders / $totalOrders) * 100;
    }

    /**
     * Calculate conversion rate (orders vs package views)
     */
    private function calculateConversionRate(Carbon $startDate): float
    {
        // This is a simplified conversion rate calculation
        // In a real implementation, you'd track package views
        $orders = Order::where('created_at', '>=', $startDate)->count();
        $estimatedViews = $orders * 10; // Rough estimate

        return $orders > 0 ? ($orders / $estimatedViews) * 100 : 0;
    }

    /**
     * Get start date based on period
     */
    private function getStartDate(string $period): Carbon
    {
        switch ($period) {
            case '7days':
                return now()->subDays(7);
            case '30days':
                return now()->subDays(30);
            case '90days':
                return now()->subDays(90);
            case '1year':
                return now()->subYear();
            case 'this_month':
                return now()->startOfMonth();
            case 'last_month':
                return now()->subMonth()->startOfMonth();
            default:
                return now()->subDays(30);
        }
    }

    /**
     * Get previous period dates for comparison
     */
    private function getPreviousPeriod(Carbon $startDate): array
    {
        $days = now()->diffInDays($startDate);
        $previousEnd = $startDate->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays($days);

        return [
            'start' => $previousStart,
            'end' => $previousEnd,
        ];
    }

    /**
     * Get real-time metrics for dashboard widgets
     */
    public function getDashboardMetrics(): array
    {
        return [
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
            'pending_orders' => Order::where('status', 'paid')->count(),
            'orders_requiring_attention' => Order::whereIn('status', ['on_hold', 'delivery_failed', 'payment_failed'])->count(),
            'this_month_orders' => Order::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
            'this_month_revenue' => Order::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->where('payment_status', 'paid')->sum('total_amount'),
        ];
    }
}