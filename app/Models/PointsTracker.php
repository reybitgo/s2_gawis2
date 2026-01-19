<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTracker extends Model
{
    protected $table = 'points_tracker';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'order_item_id',
        'ppv',
        'gpv',
        'earned_at',
        'awarded_to_user_id',
        'point_type',
        'rank_at_time',
    ];

    protected $casts = [
        'ppv' => 'decimal:2',
        'gpv' => 'decimal:2',
        'earned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function awardedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_to_user_id');
    }

    public function scopePPV($query)
    {
        return $query->where('ppv', '>', 0);
    }

    public function scopeGPV($query)
    {
        return $query->where('gpv', '>', 0);
    }
}
