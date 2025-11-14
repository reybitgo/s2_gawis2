<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'package_id',
        'product_id',
        'item_type',
        'quantity',
        'unit_price',
        'total_price',
        'points_awarded_per_item',
        'total_points_awarded',
        'package_snapshot',
        'product_snapshot',
    ];

    protected $casts = [
        'package_snapshot' => 'array',
        'product_snapshot' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the actual item (package or product) based on item_type
     */
    public function getItem()
    {
        return $this->item_type === 'package' ? $this->package : $this->product;
    }

    /**
     * Get the snapshot (package_snapshot or product_snapshot) based on item_type
     */
    public function getSnapshot(): ?array
    {
        return $this->item_type === 'package' ? $this->package_snapshot : $this->product_snapshot;
    }

    /**
     * Check if this is a package item
     */
    public function isPackage(): bool
    {
        return $this->item_type === 'package';
    }

    /**
     * Check if this is a product item
     */
    public function isProduct(): bool
    {
        return $this->item_type === 'product';
    }

    /**
     * Accessors & Mutators
     */
    public function getItemNameAttribute(): string
    {
        // Try to get name from snapshot first
        $snapshot = $this->getSnapshot();
        if ($snapshot && isset($snapshot['name'])) {
            return $snapshot['name'];
        }

        // Fall back to current item
        $item = $this->getItem();
        return $item?->name ?? 'Unknown Item';
    }

    public function getItemImageUrlAttribute(): string
    {
        // Try to get image URL from snapshot first
        $snapshot = $this->getSnapshot();
        if ($snapshot && isset($snapshot['image_url'])) {
            return $snapshot['image_url'];
        }

        // Fall back to current item
        $item = $this->getItem();
        if ($item) {
            return $item->image_url ?? asset('images/' . ($this->isPackage() ? 'package' : 'product') . '-placeholder.svg');
        }

        return asset('images/' . ($this->isPackage() ? 'package' : 'product') . '-placeholder.svg');
    }

    public function getItemDescriptionAttribute(): string
    {
        // Try to get description from snapshot first
        $snapshot = $this->getSnapshot();
        if ($snapshot && isset($snapshot['short_description'])) {
            return $snapshot['short_description'];
        }

        // Fall back to current item
        $item = $this->getItem();
        return $item?->short_description ?? '';
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return currency($this->unit_price);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return currency($this->total_price);
    }

    public function getPackageNameAttribute(): string
    {
        // Try to get name from snapshot first, then fall back to current package
        if ($this->package_snapshot && isset($this->package_snapshot['name'])) {
            return $this->package_snapshot['name'];
        }

        return $this->package?->name ?? 'Unknown Package';
    }

    public function getPackageImageUrlAttribute(): string
    {
        // Try to get image URL from snapshot first, then fall back to current package
        if ($this->package_snapshot && isset($this->package_snapshot['image_url'])) {
            return $this->package_snapshot['image_url'];
        }

        return $this->package?->image_url ?? asset('images/package-placeholder.svg');
    }

    public function getPackageDescriptionAttribute(): string
    {
        // Try to get description from snapshot first, then fall back to current package
        if ($this->package_snapshot && isset($this->package_snapshot['short_description'])) {
            return $this->package_snapshot['short_description'];
        }

        return $this->package?->short_description ?? '';
    }

    /**
     * Business Logic Methods
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
        $this->total_points_awarded = $this->points_awarded_per_item * $this->quantity;
    }

    /**
     * Static helper methods
     */
    public static function createFromCartItem(Order $order, array $cartItem): self
    {
        $orderItem = new self();
        $orderItem->order_id = $order->id;
        $orderItem->quantity = $cartItem['quantity'];
        $orderItem->unit_price = $cartItem['price'];
        $orderItem->points_awarded_per_item = $cartItem['points'] ?? $cartItem['points_awarded'] ?? 0;

        // Determine item type (backward compatible)
        if (isset($cartItem['type'])) {
            // New cart format with explicit type
            $orderItem->item_type = $cartItem['type'];
            $itemId = $cartItem['item_id'];
        } elseif (isset($cartItem['package_id'])) {
            // Legacy cart format (packages only)
            $orderItem->item_type = 'package';
            $itemId = $cartItem['package_id'];
        } else {
            throw new \Exception("Cart item missing type information");
        }

        // Process based on item type
        if ($orderItem->item_type === 'package') {
            $package = Package::find($itemId);
            if (!$package) {
                throw new \Exception("Package not found: {$itemId}");
            }

            $orderItem->package_id = $package->id;
            $orderItem->package_snapshot = [
                'name' => $package->name,
                'slug' => $package->slug,
                'short_description' => $package->short_description,
                'long_description' => $package->long_description,
                'image_url' => $package->image_url,
                'category' => $package->meta_data['category'] ?? null,
                'features' => $package->meta_data['features'] ?? [],
                'duration' => $package->meta_data['duration'] ?? null,
                'captured_at' => now()->toISOString(),
            ];
        } else {
            // Product item
            $product = Product::find($itemId);
            if (!$product) {
                throw new \Exception("Product not found: {$itemId}");
            }

            $orderItem->product_id = $product->id;
            $orderItem->product_snapshot = [
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'short_description' => $product->short_description,
                'long_description' => $product->long_description,
                'image_url' => $product->image_url,
                'category' => $product->category,
                'weight_grams' => $product->weight_grams,
                'total_unilevel_bonus' => $product->total_unilevel_bonus,
                'captured_at' => now()->toISOString(),
            ];
        }

        $orderItem->calculateTotalPrice();
        $orderItem->save();

        return $orderItem;
    }

    /**
     * Check if the package still exists and is available
     */
    public function isPackageStillAvailable(): bool
    {
        return $this->package && $this->package->isAvailable();
    }

    /**
     * Get package information from snapshot or current package
     */
    public function getPackageInfo(): array
    {
        if ($this->package_snapshot) {
            return $this->package_snapshot;
        }

        if ($this->package) {
            return [
                'name' => $this->package->name,
                'slug' => $this->package->slug,
                'short_description' => $this->package->short_description,
                'image_url' => $this->package->image_url,
                'current_price' => $this->package->price,
                'is_available' => $this->package->isAvailable(),
            ];
        }

        return [
            'name' => 'Unknown Package',
            'slug' => null,
            'short_description' => 'This package is no longer available',
            'image_url' => asset('images/package-placeholder.svg'),
            'current_price' => null,
            'is_available' => false,
        ];
    }
}