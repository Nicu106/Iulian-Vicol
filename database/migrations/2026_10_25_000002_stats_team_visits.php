<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The team's own visits, recorded but never counted (App\Support\Stats, 25.10).
 *
 * The owner tested from his phone, on the line where the panel was open: the
 * statistics left him out, as they must, and he saw "nothing saved". Now the
 * team's visits are written like anyone's — pages, seconds, photographs,
 * presses — flagged team = 1, so the panel can show "Tus visitas (no cuentan)"
 * and he can see the tracking work, while no counter, list or "ahora en la
 * web" ever includes them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stat_seen', function (Blueprint $table) {
            $table->boolean('team')->default(false);
        });
        Schema::table('stat_visits', function (Blueprint $table) {
            $table->boolean('team')->default(false);
            $table->index(['team', 'last_at']);
        });
    }

    public function down(): void
    {
        Schema::table('stat_visits', function (Blueprint $table) {
            $table->dropIndex(['team', 'last_at']);
            $table->dropColumn('team');
        });
        Schema::table('stat_seen', function (Blueprint $table) {
            $table->dropColumn('team');
        });
    }
};
