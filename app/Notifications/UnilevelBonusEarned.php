<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

class UnilevelBonusEarned extends Notification
{

    public $bonus;
    public $level;
    public $buyer;
    public $order;
    public $product;

    /**
     * Create a new notification instance.
     *
     * @param float $bonus
     * @param int $level
     * @param User $buyer
     * @param Order $order
     * @param Product $product
     */
    public function __construct(float $bonus, int $level, User $buyer, Order $order, Product $product)
    {
        $this->bonus = $bonus;
        $this->level = $level;
        $this->buyer = $buyer;
        $this->order = $order;
        $this->product = $product;
    }

    /**
     * Get the notification\'s delivery channels.
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

        return (new MailMessage)
            ->subject('🎉 New Unilevel Bonus Earned!')
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username) . '!')
            ->line("Great news! You've earned a Unilevel bonus from your network.")
            ->line('')
            ->line('**Bonus Details:**')
            ->line('💰 **Amount:** ₱' . number_format($this->bonus, 2))
            ->line('📊 **Level:** ' . $levelText)
            ->line('👤 **From:** ' . ($this->buyer->fullname ?? $this->buyer->username))
            ->line('🛍️ **Product:** ' . $this->product->name)
            ->line('🧾 **Order Number:** ' . $this->order->order_number)
            ->line('')
            ->line('This bonus has been credited to your **Unilevel Balance** (withdrawable).')
            ->action('View Dashboard', url('/dashboard'))
            ->line('')
            ->line('Keep growing your network to earn more bonuses!')
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
        $buyerName = $this->buyer->fullname ?? $this->buyer->username;

        $levelDisplay = $this->level == 1
            ? '1st Level (Direct Referral)'
            : $this->level . 'th Level';

        return [
            'type' => 'unilevel_bonus',
            'bonus' => $this->bonus,
            'level' => $this->level,
            'level_display' => $levelDisplay,
            'buyer_id' => $this->buyer->id,
            'buyer_name' => $buyerName,
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'product_name' => $this->product->name,
            'message' => sprintf(
                'You earned ₱%s from %s\'s purchase of %s! (Level %d)',
                number_format($this->bonus, 2),
                $buyerName,
                $this->product->name,
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
            'type' => 'unilevel_bonus',
            'bonus' => $this->bonus,
            'level' => $this->level,
            'buyer_name' => $buyerName,
            'order_number' => $this->order->order_number,
            'product_name' => $this->product->name,
            'message' => sprintf(
                'You earned ₱%s from %s\'s purchase of %s! (Level %d)',
                number_format($this->bonus, 2),
                $buyerName,
                $this->product->name,
                $this->level
            )
        ]);
    }
}
