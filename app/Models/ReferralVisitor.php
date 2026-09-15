<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One anonymous person who arrived through a recommendation link. No IP, no name. */
class ReferralVisitor extends Model
{
    protected $fillable = ['uuid', 'referrer_id', 'device', 'os', 'browser', 'via', 'visits', 'first_seen_at', 'last_seen_at'];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'visits'        => 'integer',
    ];

    /** The link this person is attributed to: the first one they opened. */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ReferralEvent::class, 'visitor_id');
    }
}
