<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Product extends Model
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
        'total_unilevel_bonus',
        'sku',
        'category',
        'weight_grams',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'points_awarded' => 'decimal:2',
        'quantity_available' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'meta_data' => 'array',
        'total_unilevel_bonus' => 'decimal:2',
        'weight_grams' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = Str::slug($product->name);
            }
            if (!$product->sku) {
                $product->sku = 'PROD-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::saved(function ($product) {
            Cache::forget("product_{$product->id}");
        });

        static::deleted(function ($product) {
            Cache::forget("product_{$product->id}");
        });
    }

    // Scopes
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

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Relationships
    public function unilevelSettings(): HasMany
    {
        return $this->hasMany(UnilevelSetting::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : asset('images/product-placeholder.svg');
    }

    public function getFormattedPriceAttribute()
    {
        return currency($this->price);
    }

    public function getFormattedLongDescriptionAttribute()
    {
        $content = e($this->long_description); // Escape HTML entities
        $content = preg_replace("/\n\n/", "</p><p>", $content); // Convert double newlines to paragraph breaks
        $content = nl2br($content); // Convert single newlines to <br> within paragraphs
        return "<p>{$content}</p>"; // Wrap the entire content in a paragraph
    }

    // Business Logic
    public function isAvailable(): bool
    {
        return $this->is_active &&
               ($this->quantity_available === null || $this->quantity_available > 0);
    }

    public function canBeDeleted(): bool
    {
        // Check if product_id column exists in order_items table (Phase 2 feature)
        try {
            return $this->orderItems()->count() === 0;
        } catch (\Exception $e) {
            // If order_items doesn't have product_id column yet, allow deletion
            return true;
        }
    }

    public function reduceQuantity(int $amount = 1): void
    {
        if ($this->quantity_available !== null) {
            $this->quantity_available = max(0, $this->quantity_available - $amount);
            $this->save();
        }
    }

    /**
     * Calculate total unilevel bonus for this product
     * Similar to how Package calculates total MLM commission
     */
    public function calculateTotalUnilevelBonus(): float
    {
        return $this->unilevelSettings()
            ->where('is_active', true)
            ->sum('bonus_amount');
    }

    /**
     * Update the cached total unilevel bonus
     * Should be called after unilevel settings are modified
     */
    public function updateTotalUnilevelBonus(): void
    {
        $this->update([
            'total_unilevel_bonus' => $this->calculateTotalUnilevelBonus()
        ]);
    }
}
