<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyQuotaTracker extends Model
{
    protected $table = 'monthly_quota_tracker';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'total_pv_points',
        'required_quota',
        'quota_met',
        'last_purchase_at',
    ];

    protected $casts = [
        'total_pv_points' => 'decimal:2',
        'required_quota' => 'decimal:2',
        'quota_met' => 'boolean',
        'last_purchase_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: Get or create tracker for current month
    public static function getOrCreateForCurrentMonth(User $user): self
    {
        $year = now()->year;
        $month = now()->month;

        return self::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'total_pv_points' => 0,
                'required_quota' => $user->getMonthlyQuotaRequirement(),
                'quota_met' => false,
            ]
        );
    }

    // Helper: Check if quota is met
    public function checkQuotaMet(): bool
    {
        $this->quota_met = $this->total_pv_points >= $this->required_quota;
        $this->save();
        return $this->quota_met;
    }
}
