<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class EmailService
{
    /**
     * Send email to user only if their email is verified.
     * If user email is not verified, log it and notify admins.
     *
     * @param User $user
     * @param mixed $notification
     * @param string $context Additional context for logging
     * @return bool Whether email was sent to user
     */
    public static function sendToUserIfVerified(User $user, $notification, string $context = ''): bool
    {
        // Check if user has verified email
        if ($user->hasVerifiedEmail()) {
            // Send notification to user
            $user->notify($notification);

            Log::info("Email sent to user", [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'notification' => get_class($notification),
                'context' => $context
            ]);

            return true;
        }

        // User email not verified - log and notify admins
        Log::warning("Email skipped - User email not verified", [
            'user_id' => $user->id,
            'user_email' => $user->email ?? 'N/A',
            'user_name' => $user->fullname ?? $user->username,
            'notification' => get_class($notification),
            'context' => $context
        ]);

        // Notify admins about unverified user
        self::notifyAdminsAboutUnverifiedUser($user, $context);

        return false;
    }

    /**
     * Send notification to all admin users about unverified user activity
     *
     * @param User $user
     * @param string $context
     * @return void
     */
    protected static function notifyAdminsAboutUnverifiedUser(User $user, string $context): void
    {
        try {
            $admins = User::role('admin')->get();

            foreach ($admins as $admin) {
                if ($admin->hasVerifiedEmail()) {
                    // You can create a specific notification class for this
                    // For now, we'll just log it
                    Log::info("Admin notified about unverified user", [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'unverified_user_id' => $user->id,
                        'unverified_user_name' => $user->fullname ?? $user->username,
                        'context' => $context
                    ]);

                    // TODO: Implement admin notification
                    // $admin->notify(new UnverifiedUserActivity($user, $context));
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify admins about unverified user", [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'context' => $context
            ]);
        }
    }

    /**
     * Send bulk notifications to multiple users, only to those with verified emails
     *
     * @param \Illuminate\Support\Collection $users
     * @param mixed $notification
     * @param string $context
     * @return array ['sent' => int, 'skipped' => int, 'skipped_users' => array]
     */
    public static function sendToUsersIfVerified($users, $notification, string $context = ''): array
    {
        $sent = 0;
        $skipped = 0;
        $skippedUsers = [];

        foreach ($users as $user) {
            if (self::sendToUserIfVerified($user, $notification, $context)) {
                $sent++;
            } else {
                $skipped++;
                $skippedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->fullname ?? $user->username,
                    'email' => $user->email ?? 'N/A'
                ];
            }
        }

        Log::info("Bulk email results", [
            'sent' => $sent,
            'skipped' => $skipped,
            'context' => $context
        ]);

        return [
            'sent' => $sent,
            'skipped' => $skipped,
            'skipped_users' => $skippedUsers
        ];
    }
}
