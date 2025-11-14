<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'clicked_at',
        'registered',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'registered' => 'boolean',
    ];

    /**
     * Get the user who owns the referral link
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
