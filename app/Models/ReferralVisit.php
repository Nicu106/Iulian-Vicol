<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One visitor opening one referrer's link on one day. The visitor is an HMAC, not an IP. */
class ReferralVisit extends Model
{
    protected $fillable = ['referrer_id', 'visitor', 'day'];

    protected $casts = ['day' => 'date'];
}
