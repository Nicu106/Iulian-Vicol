<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The statistics, made exact (App\Support\Stats, 25.10).
 *
 * stat_salt    the salt of each day. It lived in the application cache, and a
 *              cache:clear in the middle of a day made every visitor a new person
 *              and orphaned their open pages. Two days kept, like stat_seen.
 * stat_staff   the owner's and his team's connections: an HMAC of the IP and
 *              until when it is theirs. Their visits are not counted, signed in
 *              or not (a phone's in-app browser knows nothing of the panel).
 * stat_seen    inc  everything this person added today, so that a visit found
 *                   later to be the owner's is taken out again, number by number,
 *                   and a press is counted once per person.
 *              iph  the day's hash of the IP alone, to find all of a connection's
 *                   browsers.
 * stat_visits  place  the town the visit came from (a local GeoIP lookup; the IP
 *                     is not kept).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stat_salt', function (Blueprint $table) {
            $table->string('day', 10)->primary();
            $table->string('salt', 32);
        });

        Schema::create('stat_staff', function (Blueprint $table) {
            $table->string('ip', 32)->primary();
            $table->unsignedInteger('until');
        });

        Schema::table('stat_seen', function (Blueprint $table) {
            $table->text('inc')->nullable();
            $table->string('iph', 32)->default('');
            $table->index(['day', 'iph']);
        });

        Schema::table('stat_visits', function (Blueprint $table) {
            $table->string('place', 80)->default('');
        });

        // The salts already handed out today and yesterday move over, so the
        // people counted before this migration stay the same people.
        $now = now('Europe/Madrid');
        foreach ([$now->toDateString(), $now->copy()->subDay()->toDateString()] as $day) {
            $salt = Cache::get('stats:salt:' . $day);
            if (is_string($salt) && $salt !== '') {
                DB::table('stat_salt')->insertOrIgnore(['day' => $day, 'salt' => $salt]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('stat_visits', function (Blueprint $table) {
            $table->dropColumn('place');
        });
        Schema::table('stat_seen', function (Blueprint $table) {
            $table->dropIndex(['day', 'iph']);
            $table->dropColumn(['inc', 'iph']);
        });
        Schema::dropIfExists('stat_staff');
        Schema::dropIfExists('stat_salt');
    }
};
