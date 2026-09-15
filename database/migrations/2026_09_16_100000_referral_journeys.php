<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What happened after a recommendation link was opened.
 *
 *   referral_visitors   one anonymous person who arrived through a link: which
 *                       link they are attributed to (the first one they opened),
 *                       device / system / browser / where the link was opened
 *                       from, how many separate visits, first and last seen.
 *                       No IP, no name. Identified only by a random id in the
 *                       mc_rv cookie.
 *   referral_events     what that person did: opened a link, saw a page, saw a
 *                       car, pressed WhatsApp or e-mail, sent a form.
 *
 * Only people who came through a recommendation link are recorded; the rest of
 * the site's visitors are not. Rows are deleted 90 days after the person was
 * last seen (App\Support\Journey::prune).
 *
 * referral_visits, which only counted daily openings, is replaced: an opening
 * is now an event, and the person behind it is a visitor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_visitors', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('referrer_id')->constrained('referrers')->cascadeOnDelete();
            $t->string('device', 12)->default('desktop');   // mobile | tablet | desktop
            $t->string('os', 20)->nullable();
            $t->string('browser', 20)->nullable();
            $t->string('via', 12)->nullable();              // whatsapp | instagram | facebook | telegram | directo
            $t->unsignedInteger('visits')->default(1);
            $t->timestamp('first_seen_at')->nullable();
            $t->timestamp('last_seen_at')->nullable()->index();
            $t->timestamps();
        });

        Schema::create('referral_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('visitor_id')->constrained('referral_visitors')->cascadeOnDelete();
            $t->foreignId('referrer_id')->nullable()->constrained('referrers')->cascadeOnDelete(); // the link opened, for "open"
            $t->string('type', 16)->index();                  // open | page | car | whatsapp | email | form_sell | form_refer
            $t->string('path', 255)->nullable();
            $t->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $t->timestamp('created_at')->nullable()->index();
        });

        Schema::dropIfExists('referral_visits');
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_events');
        Schema::dropIfExists('referral_visitors');

        Schema::create('referral_visits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referrer_id')->constrained('referrers')->cascadeOnDelete();
            $t->string('visitor', 64);
            $t->date('day');
            $t->timestamps();
            $t->unique(['referrer_id', 'visitor', 'day']);
        });
    }
};
