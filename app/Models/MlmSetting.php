<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlmSetting extends Model
{
    protected $fillable = [
        'package_id',
        'level',
        'commission_amount',
        'is_active',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the package that owns the MLM setting
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get commission amount for a specific package and level
     */
    public static function getCommissionForLevel(int $packageId, int $level): float
    {
        $setting = self::where('package_id', $packageId)
            ->where('level', $level)
            ->where('is_active', true)
            ->first();

        return $setting ? (float) $setting->commission_amount : 0.00;
    }

    /**
     * Get total commission for a package (all levels)
     */
    public static function getTotalCommission(int $packageId): float
    {
        return (float) self::where('package_id', $packageId)
            ->where('is_active', true)
            ->sum('commission_amount');
    }
}
