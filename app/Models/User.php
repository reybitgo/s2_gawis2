<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'phone',
        'address',
        'address_2',
        'city',
        'state',
        'zip',
        'delivery_instructions',
        'delivery_time_preference',
        'sponsor_id',
        'referral_code',
        'network_status',
        'network_activated_at',
        'last_product_purchase_at',
        'suspended_at',
        'payment_preference',
        'gcash_number',
        'maya_number',
        'pickup_location',
        'other_payment_method',
        'other_payment_details',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'network_activated_at' => 'datetime',
            'last_product_purchase_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(\App\Models\Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function getOrCreateWallet()
    {
        return $this->wallet ?: $this->wallet()->create(['user_id' => $this->id]);
    }

    /**
     * Determine if the user must verify their email address.
     *
     * @return bool
     */
    public function mustVerifyEmail()
    {
        // Check if email verification is enabled globally
        $emailVerificationEnabled = \App\Models\SystemSetting::get('email_verification_enabled', true);

        // If disabled globally, no verification required
        if (!$emailVerificationEnabled) {
            return false;
        }

        // If enabled, use Laravel's default behavior
        return !$this->hasVerifiedEmail();
    }

    /**
     * Determine if the user has verified their email address.
     * Users without email are considered "verified" since they don't need verification.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        // Users without email are not considered verified.
        if (is_null($this->email)) {
            return false;
        }

        // Users with email need to verify it
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if user is active for network commissions.
     *
     * @return bool
     */
    public function isNetworkActive(): bool
    {
        return $this->network_status === 'active';
    }

    /**
     * Activate the user's network status.
     */
    public function activateNetwork(): void
    {
        if ($this->network_status !== 'active') {
            $this->update([
                'network_status' => 'active',
                'network_activated_at' => now(),
            ]);
        }
    }

    /**
     * Get the sponsor (upline) of this user
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    /**
     * Get all direct referrals (downline) of this user
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'sponsor_id');
    }

    /**
     * Get all referral clicks for this user's referral link
     */
    public function referralClicks()
    {
        return $this->hasMany(ReferralClick::class);
    }

    /**
     * Boot method to auto-generate referral code and validate sponsor relationships
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = self::generateReferralCode();
            }
        });

        // Validate sponsor relationship before saving (create or update)
        static::saving(function ($user) {
            if (!empty($user->sponsor_id)) {
                // Prevent self-sponsorship
                if ($user->id && $user->sponsor_id == $user->id) {
                    throw new \InvalidArgumentException('A user cannot sponsor themselves.');
                }

                // Prevent circular reference by checking if the sponsor is in this user's downline
                if ($user->id && self::wouldCreateCircularReference($user->id, $user->sponsor_id)) {
                    throw new \InvalidArgumentException('Circular sponsor reference detected. The selected sponsor is already in your downline network.');
                }
            }
        });

        // Automatically create wallet for new user
        static::created(function ($user) {
            if (!$user->wallet) {
                $user->wallet()->create([
                    'user_id' => $user->id,
                    'mlm_balance' => 0.00,
                    'purchase_balance' => 0.00,
                ]);
            }
        });
    }

    /**
     * Check if setting a sponsor would create a circular reference
     *
     * @param int $userId The user whose sponsor is being set
     * @param int $sponsorId The proposed sponsor
     * @return bool True if circular reference would be created
     */
    private static function wouldCreateCircularReference($userId, $sponsorId): bool
    {
        // If sponsor is null, no circular reference possible
        if (is_null($sponsorId)) {
            return false;
        }

        // Start from the proposed sponsor and walk up the chain
        $currentId = $sponsorId;
        $visited = [];
        $maxDepth = 100; // Prevent infinite loops in corrupted data
        $depth = 0;

        while ($currentId && $depth < $maxDepth) {
            // If we encounter the original user in the upline, it's circular
            if ($currentId == $userId) {
                return true;
            }

            // Prevent infinite loops from corrupted data
            if (in_array($currentId, $visited)) {
                // Already visited this user - there's already a circular reference in the data
                return true;
            }

            $visited[] = $currentId;

            // Get the next sponsor in the chain
            $nextSponsor = self::where('id', $currentId)->value('sponsor_id');

            if (is_null($nextSponsor)) {
                // Reached the top of the chain, no circular reference
                return false;
            }

            $currentId = $nextSponsor;
            $depth++;
        }

        // If we hit max depth, assume circular reference exists
        return $depth >= $maxDepth;
    }

    /**
     * Generate a unique referral code
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = 'REF' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
