<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnilevelSetting extends Model
{
    protected $fillable = [
        'product_id',
        'level',
        'bonus_amount',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'bonus_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get default unilevel structure for a product
     * Similar to MlmSetting default structure
     */
    public static function getDefaultStructure(float $productPrice): array
    {
        // Default example: Lower amounts than packages since products are consumable
        // Admin can customize these amounts per product
        return [
            ['level' => 1, 'bonus_amount' => 20.00], // Direct sponsor gets more
            ['level' => 2, 'bonus_amount' => 10.00],
            ['level' => 3, 'bonus_amount' => 10.00],
            ['level' => 4, 'bonus_amount' => 10.00],
            ['level' => 5, 'bonus_amount' => 10.00],
        ];
        // Total: ₱60 per product sold (example for ₱500 product = 12%)
    }
}
