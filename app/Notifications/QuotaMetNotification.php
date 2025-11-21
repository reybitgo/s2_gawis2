<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class QuotaMetNotification extends Notification
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
        return (new MailMessage)
            ->subject('🎉 Congratulations! Monthly Quota Met - ' . $this->status['month_name'] . ' ' . $this->status['year'])
            ->greeting('Congratulations ' . $notifiable->username . '!')
            ->line('You have successfully met your monthly quota!')
            ->line('**Total PV Earned:** ' . number_format($this->status['total_pv'], 2) . ' PV')
            ->line('**Required Quota:** ' . number_format($this->status['required_quota'], 2) . ' PV')
            ->line('You are now **QUALIFIED** to earn Unilevel bonuses from your downline\'s purchases this month.')
            ->action('View My Quota Status', url('/my-quota'))
            ->line('Keep up the great work and continue building your business!');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'monthly_quota_met',
            'title' => 'Monthly Quota Met!',
            'message' => 'You have met your monthly quota of ' . number_format($this->status['required_quota'], 2) . ' PV for ' . $this->status['month_name'] . ' ' . $this->status['year'],
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
            'type' => 'monthly_quota_met',
            'status' => $this->status,
        ];
    }
}
