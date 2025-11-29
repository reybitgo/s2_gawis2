<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankAdvancement extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'from_rank',
        'to_rank',
        'from_package_id',
        'to_package_id',
        'advancement_type',
        'required_sponsors',
        'sponsors_count',
        'system_paid_amount',
        'order_id',
        'notes',
    ];

    protected $casts = [
        'system_paid_amount' => 'decimal:2',
        'required_sponsors' => 'integer',
        'sponsors_count' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromPackage()
    {
        return $this->belongsTo(Package::class, 'from_package_id');
    }

    public function toPackage()
    {
        return $this->belongsTo(Package::class, 'to_package_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isSystemReward(): bool
    {
        return $this->advancement_type === 'sponsorship_reward';
    }
}
