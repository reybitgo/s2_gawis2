<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'changed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the order that owns the status history.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who changed the status (if it was a user).
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return Order::getStatusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Check if the status change was made by an admin.
     */
    public function isAdminChange(): bool
    {
        return in_array($this->changed_by, ['admin', 'system']) ||
               (is_numeric($this->changed_by) && $this->changer && $this->changer->hasRole('admin'));
    }

    /**
     * Get a human-readable description of who changed the status.
     */
    public function getChangedByDescriptionAttribute(): string
    {
        if ($this->changed_by === 'system') {
            return 'System';
        }

        if ($this->changed_by === 'admin' || $this->isAdminChange()) {
            if (is_numeric($this->changed_by) && $this->changer) {
                return $this->changer->fullname ?? $this->changer->username;
            }
            return 'Admin';
        }

        if ($this->changed_by === 'user') {
            return 'Customer';
        }

        // If numeric, try to get user info
        if (is_numeric($this->changed_by) && $this->changer) {
            return $this->changer->fullname ?? $this->changer->username;
        }

        return 'Unknown';
    }
}