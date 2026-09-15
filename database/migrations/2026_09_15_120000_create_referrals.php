<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recommendations without accounts.
 *
 *   referrers         a person with a personal link: /r/{code}
 *   referral_visits   one row per visitor per referrer per day, for the count of
 *                     link opens; the visitor is an HMAC of IP + user agent, never
 *                     the IP itself
 *   referral_rewards  one per car sold through a recommendation, with a state:
 *                     pending -> approved -> paid, or rejected
 *   vehicles.referred_by   who sent the buyer, set by the owner when he sells it
 *
 * No personal data travels in a URL: the link carries a random code and the
 * name and phone stay here. The friend's data is never asked for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrers', function (Blueprint $t) {
            $t->id();
            $t->string('code', 16)->unique();
            $t->string('name', 120);
            $t->string('phone', 20)->index();          // digits only, with country code
            $t->string('source', 12)->default('admin'); // admin | web
            $t->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete(); // the car this person bought, if any
            $t->timestamps();
        });

        Schema::create('referral_visits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referrer_id')->constrained('referrers')->cascadeOnDelete();
            $t->string('visitor', 64);
            $t->date('day');
            $t->timestamps();
            $t->unique(['referrer_id', 'visitor', 'day']);
        });

        Schema::create('referral_rewards', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referrer_id')->constrained('referrers')->cascadeOnDelete();
            $t->foreignId('vehicle_id')->unique()->constrained('vehicles')->cascadeOnDelete(); // one reward per car sold
            $t->string('status', 12)->default('pending');
            $t->string('note', 255)->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });

        Schema::table('vehicles', function (Blueprint $t) {
            $t->foreignId('referred_by')->nullable()->constrained('referrers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $t) {
            $t->dropForeign(['referred_by']);
            $t->dropColumn('referred_by');
        });
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('referral_visits');
        Schema::dropIfExists('referrers');
    }
};
