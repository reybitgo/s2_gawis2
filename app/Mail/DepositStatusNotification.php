<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;
use App\Models\User;

class DepositStatusNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $transaction;
    public $user;
    public $status;
    public $adminNotes;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, User $user, string $status, string $adminNotes = null)
    {
        $this->transaction = $transaction;
        $this->user = $user;
        $this->status = $status;
        $this->adminNotes = $adminNotes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusText = $this->status === 'approved' ? 'Approved' : 'Rejected';

        return new Envelope(
            subject: 'Deposit Request ' . $statusText . ' - $' . number_format($this->transaction->amount, 2),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.deposit-status-notification',
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
