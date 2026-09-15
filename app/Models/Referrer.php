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

    public function visits(): HasMany
    {
        return $this->hasMany(ReferralVisit::class);
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
