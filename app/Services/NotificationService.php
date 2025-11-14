<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send new user registration notification
     */
    public static function notifyNewUserRegistration($user)
    {
        if (!SystemSetting::get('notify_new_user', true)) {
            return;
        }

        $adminEmail = SystemSetting::get('admin_email', 'admin@example.com');

        try {
            Mail::raw(
                "New user has registered:\n\n" .
                "Name: {$user->name}\n" .
                "Email: {$user->email}\n" .
                "Registration Date: " . $user->created_at->format('Y-m-d H:i:s') . "\n\n" .
                "Please review the user account in the admin panel.",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('New User Registration - ' . config('app.name'));
                }
            );

            Log::info('New user registration notification sent', [
                'user_id' => $user->id,
                'admin_email' => $adminEmail
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new user notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send large transaction notification
     */
    public static function notifyLargeTransaction($transaction)
    {
        if (!SystemSetting::get('notify_large_transaction', true)) {
            return;
        }

        $threshold = SystemSetting::get('transaction_review_threshold', 1000);

        if ($transaction->amount < $threshold) {
            return;
        }

        $adminEmail = SystemSetting::get('admin_email', 'admin@example.com');

        try {
            Mail::raw(
                "Large transaction detected:\n\n" .
                "Amount: $" . number_format($transaction->amount, 2) . "\n" .
                "User: {$transaction->user->name} ({$transaction->user->email})\n" .
                "Type: {$transaction->type}\n" .
                "Date: " . $transaction->created_at->format('Y-m-d H:i:s') . "\n\n" .
                "Please review this transaction in the admin panel.",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('Large Transaction Alert - ' . config('app.name'));
                }
            );

            Log::info('Large transaction notification sent', [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'admin_email' => $adminEmail
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send large transaction notification', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send suspicious activity notification
     */
    public static function notifySuspiciousActivity($user, $reason, $details = [])
    {
        if (!SystemSetting::get('notify_suspicious', true)) {
            return;
        }

        $adminEmail = SystemSetting::get('admin_email', 'admin@example.com');

        try {
            $detailsText = '';
            foreach ($details as $key => $value) {
                $detailsText .= ucfirst($key) . ": {$value}\n";
            }

            Mail::raw(
                "Suspicious activity detected:\n\n" .
                "User: {$user->name} ({$user->email})\n" .
                "Reason: {$reason}\n" .
                "Date: " . now()->format('Y-m-d H:i:s') . "\n\n" .
                ($detailsText ? "Details:\n{$detailsText}\n" : '') .
                "Please investigate this activity immediately.",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('URGENT: Suspicious Activity Alert - ' . config('app.name'));
                }
            );

            Log::warning('Suspicious activity notification sent', [
                'user_id' => $user->id,
                'reason' => $reason,
                'details' => $details,
                'admin_email' => $adminEmail
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send suspicious activity notification', [
                'user_id' => $user->id,
                'reason' => $reason,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test notification system
     */
    public static function testNotification()
    {
        $adminEmail = SystemSetting::get('admin_email', 'admin@example.com');

        try {
            Mail::raw(
                "This is a test notification from your e-wallet system.\n\n" .
                "All notification settings are working correctly.\n\n" .
                "Current notification settings:\n" .
                "- New User Notifications: " . (SystemSetting::get('notify_new_user', true) ? 'Enabled' : 'Disabled') . "\n" .
                "- Large Transaction Notifications: " . (SystemSetting::get('notify_large_transaction', true) ? 'Enabled' : 'Disabled') . "\n" .
                "- Suspicious Activity Notifications: " . (SystemSetting::get('notify_suspicious', true) ? 'Enabled' : 'Disabled') . "\n" .
                "- Transaction Review Threshold: $" . number_format(SystemSetting::get('transaction_review_threshold', 1000), 2) . "\n\n" .
                "Test completed successfully.",
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('Notification System Test - ' . config('app.name'));
                }
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test notification', [
                'admin_email' => $adminEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}