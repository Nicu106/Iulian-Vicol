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
    // The statistics' own clean-up rides on the same daily timer
    // (motorclass-v2-prune.timer), so it happens even on a day without visits.
    $this->call('stats:prune');
    $this->call('stats:geo');
})->purpose('Delete recommendation journeys older than the link lifetime');

Artisan::command('stats:prune', function () {
    $this->info(\App\Support\Stats::prune() . ' statistics rows deleted (people older than a day, visits older than 30 days).');
})->purpose('Delete the statistics\' daily hashes, their salts and visits older than 30 days');

// The town database (App\Support\Geo): DB-IP's free "IP to City Lite", published
// monthly. Daily from the prune timer; it downloads only when the copy here is
// over 32 days old (or --force), into a temporary file swapped in whole.
Artisan::command('stats:geo {--force}', function () {
    $file = \App\Support\Geo::path();
    if (! $this->option('force') && is_file($file) && filemtime($file) > time() - 32 * 86400) {
        $this->info('Town database is current (' . date('Y-m-d', filemtime($file)) . ').');
        return 0;
    }
    @mkdir(dirname($file), 0775, true);
    foreach ([0, 1] as $back) {
        $month = date('Y-m', strtotime('first day of -' . $back . ' month'));
        $gz = $file . '.download.gz';
        $ok = @copy('https://download.db-ip.com/free/dbip-city-lite-' . $month . '.mmdb.gz', $gz);
        if (! $ok || filesize($gz) < 10_000_000) {
            @unlink($gz);
            continue;
        }
        $in = gzopen($gz, 'rb');
        $out = fopen($file . '.new', 'wb');
        while (! gzeof($in)) {
            fwrite($out, gzread($in, 1 << 20));
        }
        gzclose($in);
        fclose($out);
        @unlink($gz);
        try {
            new \MaxMind\Db\Reader($file . '.new');          // it opens: it is a database
        } catch (\Throwable $e) {
            @unlink($file . '.new');
            continue;
        }
        rename($file . '.new', $file);
        \App\Support\Geo::reset();
        $this->info('Town database updated: ' . $month . '.');
        return 0;
    }
    $this->warn('Town database not updated (download failed); the old copy stays.');
    return 0;
})->purpose('Refresh the GeoIP town database used by the statistics, monthly');

// The fortnight before the browser counted, estimated from nginx's log
// (App\Support\StatsHistory). Idempotent: replaces the "h.*" rows each time.
Artisan::command('stats:history {files?*}', function () {
    $files = $this->argument('files') ?: glob('/var/log/nginx/ivmotorclass.access.log*');
    $r = \App\Support\StatsHistory::import($files);
    $this->info($r['days'] . ' days, ' . $r['people'] . ' person-days, ' . $r['views'] . ' page views (estimated).');
})->purpose('Estimate the statistics before 24.10 from the server log');

// The map image on /contacto and in the footer. See App\Support\StaticMap.
Artisan::command('map:render', function () {
    \App\Support\StaticMap::renderAll(fn ($l) => $this->info($l));
})->purpose('Render the static Málaga map images from OpenStreetMap tiles');
