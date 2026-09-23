<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Also runs by itself now and then when a link is opened (ReferralController@visit),
// because no scheduler runs on this box.
Artisan::command('referrals:prune', function () {
    $this->info(\App\Support\Journey::prune() . ' referred visitors not seen for ' . \App\Support\Referral::days() . ' days deleted.');
})->purpose('Delete recommendation journeys older than the link lifetime');

// The map image on /contacto and in the footer. See App\Support\StaticMap.
Artisan::command('map:render', function () {
    \App\Support\StaticMap::renderAll(fn ($l) => $this->info($l));
})->purpose('Render the static Málaga map images from OpenStreetMap tiles');
