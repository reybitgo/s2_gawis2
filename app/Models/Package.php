<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'points_awarded',
        'quantity_available',
        'short_description',
        'long_description',
        'image_path',
        'is_active',
        'sort_order',
        'meta_data',
        'is_mlm_package',
        'max_mlm_levels',
        'monthly_quota_points',
        'enforce_monthly_quota',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'points_awarded' => 'integer',
        'quantity_available' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'meta_data' => 'array',
        'is_mlm_package' => 'boolean',
        'max_mlm_levels' => 'integer',
        'monthly_quota_points' => 'decimal:2',
        'enforce_monthly_quota' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            if (!$package->slug) {
                $package->slug = Str::slug($package->name);
            }
        });

        static::updating(function ($package) {
            if ($package->isDirty('name') && !$package->isDirty('slug')) {
                $package->slug = Str::slug($package->name);
            }
        });

        // Clear cache when package is created, updated, or deleted
        static::saved(function ($package) {
            Cache::forget("package_{$package->id}");
        });

        static::deleted(function ($package) {
            Cache::forget("package_{$package->id}");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function($q) {
            $q->whereNull('quantity_available')
              ->orWhere('quantity_available', '>', 0);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::url($this->image_path) : asset('images/package-placeholder.svg');
    }

    public function getFormattedPriceAttribute()
    {
        return currency($this->price);
    }

    public function isAvailable()
    {
        return $this->is_active &&
               ($this->quantity_available === null || $this->quantity_available > 0);
    }

    public function canBeDeleted()
    {
        // Check if package has any order items (Phase 3 implementation)
        return $this->orderItems()->count() === 0;
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reduceQuantity($amount = 1)
    {
        if ($this->quantity_available !== null) {
            $this->quantity_available = max(0, $this->quantity_available - $amount);
            $this->save();
        }
    }

    /**
     * Get MLM settings for this package
     */
    public function mlmSettings()
    {
        return $this->hasMany(MlmSetting::class);
    }

    /**
     * Check if this is an MLM package
     */
    public function isMLMPackage(): bool
    {
        return (bool) $this->is_mlm_package;
    }
}
