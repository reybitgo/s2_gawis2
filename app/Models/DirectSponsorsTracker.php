<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectSponsorsTracker extends Model
{
    public $timestamps = false;
    
    protected $table = 'direct_sponsors_tracker';
    
    protected $fillable = [
        'user_id',
        'sponsored_user_id',
        'sponsored_at',
        'sponsored_user_rank_at_time',
        'sponsored_user_package_id',
        'counted_for_rank',
    ];

    protected $casts = [
        'sponsored_at' => 'datetime',
    ];

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sponsoredUser()
    {
        return $this->belongsTo(User::class, 'sponsored_user_id');
    }

    public function sponsoredUserPackage()
    {
        return $this->belongsTo(Package::class, 'sponsored_user_package_id');
    }
}
