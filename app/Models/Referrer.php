<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A person with a personal recommendation link. See App\Support\Referral. */
class Referrer extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'source', 'vehicle_id'];

    /** The car this person bought, when the link was made after a sale. */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Every time this link was opened by a person (link previews are not counted). */
    public function opens(): HasMany
    {
        return $this->hasMany(ReferralEvent::class, 'referrer_id')->where('type', 'open');
    }

    /** The people attributed to this link: those for whom it was the first link opened. */
    public function visitors(): HasMany
    {
        return $this->hasMany(ReferralVisitor::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /** Cars whose buyer this person sent. */
    public function referredVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'referred_by');
    }

    public function getLinkAttribute(): string
    {
        return url('/r/' . $this->code);
    }
}
