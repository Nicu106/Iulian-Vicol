<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The panel's statistics (App\Support\Stats).
 *
 * stats       one counter per day: day + metric + key → n. "views / ''", "car /
 *             bmw-318d-2020", "wa / /coche/bmw-318d-2020", "source / google".
 *             Nothing in it says who: only how many.
 * stat_seen   today's (and yesterday's) visitors as a 32-character hash of a
 *             salt that changes every day, the IP and the browser. It tells a
 *             second page from a new person and is deleted after two days, with
 *             the salt, so a person cannot be followed from one day to the next.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('day', 10);
            $table->string('metric', 16);
            $table->string('key', 160)->default('');
            $table->unsignedInteger('n')->default(0);
            $table->unique(['day', 'metric', 'key']);
            $table->index(['metric', 'day']);
        });

        Schema::create('stat_seen', function (Blueprint $table) {
            $table->string('day', 10);
            $table->string('hash', 32);
            $table->unsignedInteger('last_at');
            $table->string('last_path', 160)->default('');
            $table->primary(['day', 'hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stat_seen');
        Schema::dropIfExists('stats');
    }
};
