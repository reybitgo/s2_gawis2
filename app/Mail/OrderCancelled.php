<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;
    public ?string $reason;
    public bool $refundProcessed;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $reason = null, bool $refundProcessed = false)
    {
        $this->order = $order;
        $this->reason = $reason;
        $this->refundProcessed = $refundProcessed;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Cancelled - {$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.cancelled',
            with: [
                'order' => $this->order,
                'reason' => $this->reason,
                'refundProcessed' => $this->refundProcessed,
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
