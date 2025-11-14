<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FraudDetectionService
{
    /**
     * Check for velocity-based fraud patterns
     *
     * @param User $user
     * @param string $ipAddress
     * @return array
     */
    public function checkVelocity(User $user, string $ipAddress): array
    {
        $issues = [];

        // Check 1: Multiple orders in short timeframe (per user)
        $recentOrders = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentOrders >= 5) {
            $issues[] = [
                'type' => 'high_order_velocity_user',
                'severity' => 'high',
                'message' => "User placed {$recentOrders} orders in the last hour",
                'data' => ['count' => $recentOrders, 'user_id' => $user->id]
            ];
        }

        // Check 2: Multiple orders from same IP
        $ipOrders = Order::whereRaw("JSON_EXTRACT(metadata, '$.user_ip') = ?", [$ipAddress])
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($ipOrders >= 10) {
            $issues[] = [
                'type' => 'high_order_velocity_ip',
                'severity' => 'critical',
                'message' => "{$ipOrders} orders from IP {$ipAddress} in the last hour",
                'data' => ['count' => $ipOrders, 'ip' => $ipAddress]
            ];
        }

        // Check 3: Rapid failed payment attempts
        $failedAttempts = $this->getFailedPaymentAttempts($user->id, $ipAddress);
        if ($failedAttempts >= 3) {
            $issues[] = [
                'type' => 'multiple_failed_payments',
                'severity' => 'medium',
                'message' => "{$failedAttempts} failed payment attempts detected",
                'data' => ['count' => $failedAttempts]
            ];
        }

        // Check 4: Unusual order amount
        $avgOrderAmount = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->where('id', '!=', null)
            ->avg('total_amount') ?? 0;

        return [
            'is_suspicious' => !empty($issues),
            'issues' => $issues,
            'risk_score' => $this->calculateRiskScore($issues),
            'user_avg_order' => $avgOrderAmount
        ];
    }

    /**
     * Check if IP address is blacklisted or suspicious
     *
     * @param string $ipAddress
     * @return array
     */
    public function checkIpReputation(string $ipAddress): array
    {
        $issues = [];

        // Check if IP is in blacklist cache
        $blacklisted = Cache::get("ip_blacklist_{$ipAddress}", false);
        if ($blacklisted) {
            $issues[] = [
                'type' => 'blacklisted_ip',
                'severity' => 'critical',
                'message' => 'IP address is blacklisted',
                'data' => ['ip' => $ipAddress]
            ];
        }

        // Check if IP has too many users
        $usersFromIp = DB::table('orders')
            ->whereRaw("JSON_EXTRACT(metadata, '$.user_ip') = ?", [$ipAddress])
            ->distinct('user_id')
            ->count('user_id');

        if ($usersFromIp > 5) {
            $issues[] = [
                'type' => 'shared_ip_abuse',
                'severity' => 'medium',
                'message' => "Multiple users ({$usersFromIp}) ordering from same IP",
                'data' => ['ip' => $ipAddress, 'user_count' => $usersFromIp]
            ];
        }

        return [
            'is_suspicious' => !empty($issues),
            'issues' => $issues,
            'is_blacklisted' => $blacklisted
        ];
    }

    /**
     * Check for suspicious order patterns
     *
     * @param array $orderData
     * @param User $user
     * @return array
     */
    public function checkOrderPattern(array $orderData, User $user): array
    {
        $issues = [];

        // Check 1: Very large order for new user
        $userOrderCount = Order::where('user_id', $user->id)->count();
        if ($userOrderCount === 0 && $orderData['total_amount'] > 500) {
            $issues[] = [
                'type' => 'large_first_order',
                'severity' => 'medium',
                'message' => 'Unusually large first order',
                'data' => ['amount' => $orderData['total_amount']]
            ];
        }

        // Check 2: Rapid order amount escalation
        $lastOrder = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOrder && $orderData['total_amount'] > ($lastOrder->total_amount * 3)) {
            $issues[] = [
                'type' => 'rapid_amount_escalation',
                'severity' => 'medium',
                'message' => 'Order amount 3x higher than previous order',
                'data' => [
                    'current_amount' => $orderData['total_amount'],
                    'last_amount' => $lastOrder->total_amount
                ]
            ];
        }

        return [
            'is_suspicious' => !empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Calculate overall risk score (0-100)
     *
     * @param array $issues
     * @return int
     */
    private function calculateRiskScore(array $issues): int
    {
        $score = 0;

        foreach ($issues as $issue) {
            switch ($issue['severity']) {
                case 'critical':
                    $score += 40;
                    break;
                case 'high':
                    $score += 25;
                    break;
                case 'medium':
                    $score += 15;
                    break;
                case 'low':
                    $score += 5;
                    break;
            }
        }

        return min(100, $score);
    }

    /**
     * Get failed payment attempts for user/IP
     *
     * @param int $userId
     * @param string $ipAddress
     * @return int
     */
    private function getFailedPaymentAttempts(int $userId, string $ipAddress): int
    {
        $cacheKey = "failed_payments_{$userId}_{$ipAddress}";
        return Cache::get($cacheKey, 0);
    }

    /**
     * Record a failed payment attempt
     *
     * @param int $userId
     * @param string $ipAddress
     * @return void
     */
    public function recordFailedPayment(int $userId, string $ipAddress): void
    {
        $cacheKey = "failed_payments_{$userId}_{$ipAddress}";
        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addHour());

        // Auto-blacklist IP after 10 failed attempts
        if ($attempts >= 10) {
            $this->blacklistIp($ipAddress, 'Multiple failed payment attempts');
        }
    }

    /**
     * Blacklist an IP address
     *
     * @param string $ipAddress
     * @param string $reason
     * @return void
     */
    public function blacklistIp(string $ipAddress, string $reason): void
    {
        Cache::put("ip_blacklist_{$ipAddress}", true, now()->addDays(7));

        Log::warning('IP address blacklisted', [
            'ip' => $ipAddress,
            'reason' => $reason,
            'expires_at' => now()->addDays(7)->toISOString()
        ]);
    }

    /**
     * Log suspicious activity
     *
     * @param User $user
     * @param string $activityType
     * @param array $details
     * @return void
     */
    public function logSuspiciousActivity(User $user, string $activityType, array $details): void
    {
        Log::warning('Suspicious activity detected', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'activity_type' => $activityType,
            'details' => $details,
            'timestamp' => now()->toISOString()
        ]);

        // Store in cache for fraud dashboard
        $cacheKey = "suspicious_activity_{$user->id}_" . now()->format('Y-m-d-H');
        $activities = Cache::get($cacheKey, []);
        $activities[] = [
            'type' => $activityType,
            'details' => $details,
            'timestamp' => now()->toISOString()
        ];
        Cache::put($cacheKey, $activities, now()->addDay());
    }

    /**
     * Check if user should be blocked from ordering
     *
     * @param User $user
     * @param string $ipAddress
     * @return bool
     */
    public function shouldBlockOrder(User $user, string $ipAddress): bool
    {
        // Check if IP is blacklisted
        if (Cache::get("ip_blacklist_{$ipAddress}", false)) {
            return true;
        }

        // Check if user has too many failed orders recently
        $failedOrders = Order::where('user_id', $user->id)
            ->where('payment_status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($failedOrders >= 5) {
            return true;
        }

        return false;
    }
}