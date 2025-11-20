<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class QuotaReminderNotification extends Notification
{
    public $currentPV;
    public $requiredQuota;
    public $remainingPV;
    public $monthName;
    public $year;
    public $daysRemaining;

    /**
     * Create a new notification instance.
     *
     * @param float $currentPV
     * @param float $requiredQuota
     * @param float $remainingPV
     * @param string $monthName
     * @param int $year
     * @param int $daysRemaining
     */
    public function __construct(
        float $currentPV,
        float $requiredQuota,
        float $remainingPV,
        string $monthName,
        int $year,
        int $daysRemaining
    ) {
        $this->currentPV = $currentPV;
        $this->requiredQuota = $requiredQuota;
        $this->remainingPV = $remainingPV;
        $this->monthName = $monthName;
        $this->year = $year;
        $this->daysRemaining = $daysRemaining;
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
        $progressPercentage = $this->requiredQuota > 0
            ? ($this->currentPV / $this->requiredQuota) * 100
            : 0;

        return (new MailMessage)
            ->subject('⚠️ Monthly Quota Reminder - ' . $this->monthName . ' ' . $this->year)
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username) . '!')
            ->line("This is a friendly reminder about your monthly quota status for " . $this->monthName . " " . $this->year . ".")
            ->line('')
            ->line('**Current Status:**')
            ->line('⚠️ **Status:** NOT QUALIFIED')
            ->line('📊 **Current PV:** ' . number_format($this->currentPV, 2) . ' PV')
            ->line('🎯 **Required Quota:** ' . number_format($this->requiredQuota, 2) . ' PV')
            ->line('📉 **Remaining:** ' . number_format($this->remainingPV, 2) . ' PV')
            ->line('📅 **Days Left:** ' . $this->daysRemaining . ' days')
            ->line('📈 **Progress:** ' . number_format($progressPercentage, 1) . '%')
            ->line('')
            ->line('**What You Need to Do:**')
            ->line('• Purchase products worth ' . number_format($this->remainingPV, 2) . ' PV to qualify')
            ->line('• Meet your quota to earn Unilevel bonuses from your downline')
            ->line('• Act now - you have only ' . $this->daysRemaining . ' days remaining!')
            ->line('')
            ->action('Shop Products Now', url('/products'))
            ->line('')
            ->line('**Important:** If you don\'t meet your quota by the end of this month, you won\'t be able to earn Unilevel bonuses from your downline\'s purchases.')
            ->line('')
            ->action('View My Quota Status', url('/my-quota'))
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
            'type' => 'quota_reminder',
            'current_pv' => $this->currentPV,
            'required_quota' => $this->requiredQuota,
            'remaining_pv' => $this->remainingPV,
            'month_name' => $this->monthName,
            'year' => $this->year,
            'days_remaining' => $this->daysRemaining,
            'message' => sprintf(
                'Monthly quota reminder: You need %s more PV to qualify. %d days remaining.',
                number_format($this->remainingPV, 2),
                $this->daysRemaining
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
            'type' => 'quota_reminder',
            'current_pv' => $this->currentPV,
            'required_quota' => $this->requiredQuota,
            'remaining_pv' => $this->remainingPV,
            'month_name' => $this->monthName,
            'year' => $this->year,
            'days_remaining' => $this->daysRemaining,
            'message' => sprintf(
                'Monthly quota reminder: You need %s more PV to qualify. %d days remaining.',
                number_format($this->remainingPV, 2),
                $this->daysRemaining
            )
        ]);
    }
}
