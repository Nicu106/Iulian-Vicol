<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** What is owed for one car sold through a recommendation. */
class ReferralReward extends Model
{
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const PAID     = 'paid';
    public const REJECTED = 'rejected';

    /** Still waiting for the owner to do something. */
    public const OPEN = [self::PENDING, self::APPROVED];

    protected $fillable = ['referrer_id', 'vehicle_id', 'status', 'note', 'approved_at', 'paid_at'];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
