<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'user_id',
        'quantity',
        'session_id',
        'expires_at',
        'status',
        'reference',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the package that is reserved
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the user who made the reservation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if reservation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if reservation is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Mark reservation as completed
     */
    public function complete(string $orderNumber): void
    {
        $this->update([
            'status' => 'completed',
            'reference' => $orderNumber,
        ]);
    }

    /**
     * Mark reservation as cancelled
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Mark reservation as expired
     */
    public function expire(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Scope: Active reservations only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope: Expired reservations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '<=', now());
    }
}