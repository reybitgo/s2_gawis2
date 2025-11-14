<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'level',
        'type',
        'event',
        'message',
        'user_id',
        'ip_address',
        'user_agent',
        'transaction_id',
        'order_id',
        'related_user_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related user (e.g., who triggered the commission)
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get the associated transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the associated order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Static method to create a log entry
     */
    public static function createLog(
        string $type,
        string $event,
        string $message,
        string $level = 'INFO',
        ?int $userId = null,
        ?array $metadata = null,
        ?int $transactionId = null,
        ?int $orderId = null,
        ?int $relatedUserId = null
    ): self {
        return self::create([
            'level' => $level,
            'type' => $type,
            'event' => $event,
            'message' => $message,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'transaction_id' => $transactionId,
            'order_id' => $orderId,
            'related_user_id' => $relatedUserId,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log MLM commission event
     */
    public static function logMLMCommission(
        User $recipient,
        float $amount,
        int $level,
        User $buyer,
        Order $order,
        ?int $packageId = null,
        ?string $packageName = null
    ): self {
        return self::createLog(
            type: 'mlm_commission',
            event: 'commission_earned',
            message: sprintf(
                '%s earned ₱%s Level %d commission from %s\'s order #%s',
                $recipient->username ?? $recipient->fullname ?? 'User',
                number_format($amount, 2),
                $level,
                $buyer->username ?? $buyer->fullname ?? 'User',
                $order->order_number
            ),
            level: 'INFO',
            userId: $recipient->id,
            metadata: [
                'commission_amount' => $amount,
                'commission_level' => $level,
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->username ?? $buyer->fullname ?? 'Unknown',
                'order_number' => $order->order_number,
                'package_id' => $packageId,
                'package_name' => $packageName,
            ],
            orderId: $order->id,
            relatedUserId: $buyer->id
        );
    }

    /**
     * Log Unilevel bonus event
     */
    public static function logUnilevelBonus(
        User $recipient,
        float $amount,
        int $level,
        User $buyer,
        Order $order,
        ?int $productId = null,
        ?string $productName = null
    ): self {
        return self::createLog(
            type: 'unilevel_bonus',
            event: 'bonus_earned',
            message: sprintf(
                '%s earned ₱%s Level %d Unilevel bonus from %s\'s order #%s',
                $recipient->username ?? $recipient->fullname ?? 'User',
                number_format($amount, 2),
                $level,
                $buyer->username ?? $buyer->fullname ?? 'User',
                $order->order_number
            ),
            level: 'INFO',
            userId: $recipient->id,
            metadata: [
                'bonus_amount' => $amount,
                'bonus_level' => $level,
                'buyer_id' => $buyer->id,
                'buyer_name' => $buyer->username ?? $buyer->fullname ?? 'Unknown',
                'order_number' => $order->order_number,
                'product_id' => $productId,
                'product_name' => $productName,
            ],
            orderId: $order->id,
            relatedUserId: $buyer->id
        );
    }

    /**
     * Log wallet transaction event
     */
    public static function logWalletTransaction(
        string $event,
        string $message,
        Transaction $transaction,
        string $level = 'INFO'
    ): self {
        return self::createLog(
            type: 'wallet',
            event: $event,
            message: $message,
            level: $level,
            userId: $transaction->user_id,
            metadata: [
                'transaction_type' => $transaction->type,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'payment_method' => $transaction->payment_method,
                'reference_number' => $transaction->reference_number,
            ],
            transactionId: $transaction->id
        );
    }

    /**
     * Log order event
     */
    public static function logOrder(
        string $event,
        string $message,
        Order $order,
        string $level = 'INFO',
        ?array $additionalMetadata = null
    ): self {
        $metadata = [
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
        ];

        if ($additionalMetadata) {
            $metadata = array_merge($metadata, $additionalMetadata);
        }

        return self::createLog(
            type: 'order',
            event: $event,
            message: $message,
            level: $level,
            userId: $order->user_id,
            metadata: $metadata,
            orderId: $order->id
        );
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by level
     */
    public function scopeOfLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to filter by event
     */
    public function scopeOfEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to search logs
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('message', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%")
              ->orWhere('event', 'like', "%{$search}%");
        });
    }
}
