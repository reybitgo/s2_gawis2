<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class QuotaReminderNotification extends Notification
{
    use Queueable;

    protected array $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $status)
    {
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysLeft = now()->endOfMonth()->diffInDays(now());
        
        return (new MailMessage)
            ->subject('⏰ Monthly Quota Reminder - ' . $this->status['month_name'] . ' ' . $this->status['year'])
            ->greeting('Hello ' . $notifiable->username . '!')
            ->line('This is a friendly reminder about your monthly quota status.')
            ->line('**Current Progress:** ' . number_format($this->status['total_pv'], 2) . ' / ' . number_format($this->status['required_quota'], 2) . ' PV (' . number_format($this->status['progress_percentage'], 1) . '%)')
            ->line('**Remaining:** ' . number_format($this->status['remaining_pv'], 2) . ' PV')
            ->line('**Days Left:** ' . $daysLeft . ' days until month end')
            ->line('You need to accumulate **' . number_format($this->status['remaining_pv'], 2) . ' more PV** this month to qualify for Unilevel bonuses.')
            ->action('Shop Products to Earn PV', url('/products'))
            ->line('Purchase products to earn PV points and meet your monthly quota!')
            ->line('Don\'t miss out on earning Unilevel bonuses from your downline\'s purchases.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'monthly_quota_reminder',
            'title' => 'Monthly Quota Reminder',
            'message' => 'You need ' . number_format($this->status['remaining_pv'], 2) . ' more PV to meet your quota for ' . $this->status['month_name'] . ' ' . $this->status['year'],
            'status' => $this->status,
            'action_url' => url('/my-quota'),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'monthly_quota_reminder',
            'status' => $this->status,
        ];
    }
}
