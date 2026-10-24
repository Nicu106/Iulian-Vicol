<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The visits feed and the per-car numbers (App\Support\Stats).
 *
 * stat_visits  one row per visit: what the browser announces (mobile / iOS /
 *              Safari), where it came from, and the pages and presses in order
 *              with their times. No IP, no name, nothing typed. Deleted after
 *              30 days.
 * stat_seen    gains what is needed to join a page to its visit and to count a
 *              person once per car per day: the visit's id, its source, and the
 *              cars already seen today.
 * stats.key    indexed, because one car's numbers are read by key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stat_visits', function (Blueprint $table) {
            $table->id();
            $table->string('day', 10);
            $table->unsignedInteger('started_at');
            $table->unsignedInteger('last_at');
            $table->string('device', 8)->default('');
            $table->string('os', 12)->default('');
            $table->string('browser', 12)->default('');
            $table->string('source', 16)->default('');
            $table->string('landing', 160)->default('');
            $table->text('steps');
            $table->unsignedSmallInteger('pages')->default(0);
            $table->boolean('wa')->default(false);
            $table->index('day');
            $table->index('last_at');
        });

        Schema::table('stat_seen', function (Blueprint $table) {
            $table->unsignedBigInteger('visit')->nullable();
            $table->string('source', 16)->default('');
            $table->text('cars')->nullable();
            $table->index('last_at');
        });

        Schema::table('stats', function (Blueprint $table) {
            $table->index(['key', 'day']);
        });
    }

    public function down(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropIndex(['key', 'day']);
        });
        Schema::table('stat_seen', function (Blueprint $table) {
            $table->dropIndex(['last_at']);
            $table->dropColumn(['visit', 'source', 'cars']);
        });
        Schema::dropIfExists('stat_visits');
    }
};
