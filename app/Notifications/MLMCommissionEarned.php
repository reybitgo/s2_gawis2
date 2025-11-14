<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Order;

class MLMCommissionEarned extends Notification
{

    public $commission;
    public $level;
    public $buyer;
    public $order;

    /**
     * Create a new notification instance.
     *
     * @param float $commission
     * @param int $level
     * @param User $buyer
     * @param Order $order
     */
    public function __construct(float $commission, int $level, User $buyer, Order $order)
    {
        $this->commission = $commission;
        $this->level = $level;
        $this->buyer = $buyer;
        $this->order = $order;
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
        $levelText = $this->level == 1
            ? '1st Level (Direct Referral)'
            : $this->level . 'th Level (Indirect Referral)';

        // Get first MLM package from order items
        $mlmPackage = $this->order->orderItems->first(function($item) {
            return $item->package && $item->package->is_mlm_package;
        });

        $packageName = $mlmPackage && $mlmPackage->package
            ? $mlmPackage->package->name
            : 'Package';

        return (new MailMessage)
            ->subject('🎉 New MLM Commission Earned!')
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username) . '!')
            ->line("Great news! You've earned a commission from your network.")
            ->line('')
            ->line('**Commission Details:**')
            ->line('💰 **Amount:** ₱' . number_format($this->commission, 2))
            ->line('📊 **Level:** ' . $levelText)
            ->line('👤 **From:** ' . ($this->buyer->fullname ?? $this->buyer->username))
            ->line('📦 **Package:** ' . $packageName)
            ->line('🧾 **Order Number:** ' . $this->order->order_number)
            ->line('')
            ->line('This commission has been credited to your **MLM Balance** (withdrawable).')
            ->action('View Dashboard', url('/dashboard'))
            ->line('')
            ->line('Keep building your network to earn more commissions!')
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
        // Get first MLM package from order items
        $mlmPackage = $this->order->orderItems->first(function($item) {
            return $item->package && $item->package->is_mlm_package;
        });

        $packageName = $mlmPackage && $mlmPackage->package
            ? $mlmPackage->package->name
            : 'Package';

        $buyerName = $this->buyer->fullname ?? $this->buyer->username;

        $levelDisplay = $this->level == 1
            ? '1st Level (Direct Referral)'
            : $this->level . 'th Level';

        return [
            'type' => 'mlm_commission',
            'commission' => $this->commission,
            'level' => $this->level,
            'level_display' => $levelDisplay,
            'buyer_id' => $this->buyer->id,
            'buyer_name' => $buyerName,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'package_name' => $packageName,
            'message' => sprintf(
                'You earned ₱%s from %s\'s purchase! (Level %d)',
                number_format($this->commission, 2),
                $buyerName,
                $this->level
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
        $buyerName = $this->buyer->fullname ?? $this->buyer->username;

        return new BroadcastMessage([
            'type' => 'mlm_commission',
            'commission' => $this->commission,
            'level' => $this->level,
            'buyer_name' => $buyerName,
            'order_number' => $this->order->order_number,
            'message' => sprintf(
                'You earned ₱%s from %s\'s purchase! (Level %d)',
                number_format($this->commission, 2),
                $buyerName,
                $this->level
            )
        ]);
    }
}
