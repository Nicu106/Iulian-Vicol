<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Support\Referral;

/**
 * One place that keeps rewards in step with cars. The edit form, "Marcar
 * vendido" and the bulk actions all save a Vehicle; none of them has to
 * remember the referral rules.
 */
class ReferralObserver
{
    public function saved(Vehicle $vehicle): void
    {
        if ($vehicle->wasRecentlyCreated || $vehicle->wasChanged(['status', 'referred_by', 'buyer_phone'])) {
            Referral::syncReward($vehicle);
        }
    }
}
