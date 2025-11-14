<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'description',
        'images',
        'status',
        'admin_response',
        'return_tracking_number',
        'approved_at',
        'rejected_at',
        'refunded_at',
    ];

    protected $casts = [
        'images' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Return Reasons
    const REASON_DAMAGED_PRODUCT = 'damaged_product';
    const REASON_WRONG_ITEM = 'wrong_item';
    const REASON_NOT_AS_DESCRIBED = 'not_as_described';
    const REASON_QUALITY_ISSUE = 'quality_issue';
    const REASON_NO_LONGER_NEEDED = 'no_longer_needed';
    const REASON_OTHER = 'other';

    // Return Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper Methods
     */
    public static function getReasonLabels(): array
    {
        return [
            self::REASON_DAMAGED_PRODUCT => 'Damaged Product',
            self::REASON_WRONG_ITEM => 'Wrong Item Received',
            self::REASON_NOT_AS_DESCRIBED => 'Not as Described',
            self::REASON_QUALITY_ISSUE => 'Quality Issue',
            self::REASON_NO_LONGER_NEEDED => 'No Longer Needed',
            self::REASON_OTHER => 'Other',
        ];
    }

    public static function getStatusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_COMPLETED => 'Completed & Refunded',
        ];
    }

    public function getReasonLabelAttribute(): string
    {
        return self::getReasonLabels()[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge bg-warning',
            self::STATUS_APPROVED => 'badge bg-info',
            self::STATUS_REJECTED => 'badge bg-danger',
            self::STATUS_COMPLETED => 'badge bg-success',
            default => 'badge bg-secondary',
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function approve(string $adminResponse): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'admin_response' => $adminResponse,
            'approved_at' => now(),
        ]);

        // Update order status to return_approved
        $this->order->updateStatus(
            Order::STATUS_RETURN_APPROVED,
            'Return request approved by admin',
            'admin'
        );
    }

    public function reject(string $adminResponse): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'admin_response' => $adminResponse,
            'rejected_at' => now(),
        ]);

        // Revert order status back to delivered
        $this->order->updateStatus(
            Order::STATUS_DELIVERED,
            'Return request rejected by admin',
            'admin'
        );
    }

    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'refunded_at' => now(),
        ]);
    }

    public function updateTrackingNumber(string $trackingNumber): void
    {
        $this->update([
            'return_tracking_number' => $trackingNumber,
        ]);

        // Update order status to return_in_transit
        $this->order->updateStatus(
            Order::STATUS_RETURN_IN_TRANSIT,
            "Customer shipped return with tracking: {$trackingNumber}",
            'customer'
        );
    }
}
