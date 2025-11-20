<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QuotaMetNotification extends Notification
{
    public $totalPV;
    public $requiredQuota;
    public $monthName;
    public $year;

    /**
     * Create a new notification instance.
     *
     * @param float $totalPV
     * @param float $requiredQuota
     * @param string $monthName
     * @param int $year
     */
    public function __construct(float $totalPV, float $requiredQuota, string $monthName, int $year)
    {
        $this->totalPV = $totalPV;
        $this->requiredQuota = $requiredQuota;
        $this->monthName = $monthName;
        $this->year = $year;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        // Only send email if user has verified email
        if ($notifiable->hasVerifiedEmail()) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Congratulations! Monthly Quota Achieved!')
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username) . '!')
            ->line("Excellent news! You've met your monthly quota and are now qualified to receive Unilevel bonuses!")
            ->line('')
            ->line('**Quota Achievement Details:**')
            ->line('✅ **Status:** QUALIFIED')
            ->line('📊 **PV Earned:** ' . number_format($this->totalPV, 2) . ' PV')
            ->line('🎯 **Required Quota:** ' . number_format($this->requiredQuota, 2) . ' PV')
            ->line('📅 **Month:** ' . $this->monthName . ' ' . $this->year)
            ->line('')
            ->line('**What This Means:**')
            ->line('• You can now earn Unilevel bonuses from your downline\'s purchases this month')
            ->line('• Your network contributions will generate income for you')
            ->line('• Continue purchasing products to maintain your qualification')
            ->line('')
            ->action('View My Quota Status', url('/my-quota'))
            ->line('')
            ->line('Keep up the excellent work! Remember, quotas reset on the 1st of each month.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification (for database).
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'quota_met',
            'total_pv' => $this->totalPV,
            'required_quota' => $this->requiredQuota,
            'month_name' => $this->monthName,
            'year' => $this->year,
            'message' => sprintf(
                'Congratulations! You\'ve met your monthly quota for %s %s with %s PV!',
                $this->monthName,
                $this->year,
                number_format($this->totalPV, 2)
            )
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'quota_met',
            'total_pv' => $this->totalPV,
            'required_quota' => $this->requiredQuota,
            'month_name' => $this->monthName,
            'year' => $this->year,
            'message' => sprintf(
                'Congratulations! You\'ve met your monthly quota for %s %s with %s PV!',
                $this->monthName,
                $this->year,
                number_format($this->totalPV, 2)
            )
        ]);
    }
}
