<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnverifiedUserOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public User $user;
    public string $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, User $user, string $newStatus)
    {
        $this->order = $order;
        $this->user = $user;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Unverified User Order Activity - ' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.unverified-user-order',
            with: [
                'order' => $this->order,
                'user' => $this->user,
                'newStatus' => $this->newStatus,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
