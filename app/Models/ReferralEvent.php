<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One thing a referred visitor did. Written once, never updated. */
class ReferralEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['visitor_id', 'referrer_id', 'type', 'path', 'vehicle_id', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(ReferralVisitor::class, 'visitor_id');
    }

    /** For an "open": the link that was opened, which may not be the one the person is attributed to. */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
