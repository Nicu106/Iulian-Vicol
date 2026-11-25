<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every text of the site, and the new wording the client writes for each one at
 * /muestras/textos (App\Http\Controllers\SiteTextController). The texts come
 * from database/data/site-texts.json (php artisan texts:sync).
 *
 * site_text_edits keeps every version ever saved, in order, and is never
 * updated or deleted: the client's work cannot be lost by a later save.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key', 16)->unique();         // sha1 of section + text: a re-sync keeps the edits
            $table->string('section', 160);
            $table->unsignedInteger('position');
            $table->text('current_text');                 // the text as the site has it (not 'original': Eloquent's own property)
            $table->text('proposed')->nullable();         // the latest new wording; null or '' = none yet
            $table->timestamps();
        });

        Schema::create('site_text_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_text_id')->constrained()->restrictOnDelete();
            $table->text('value')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_text_edits');
        Schema::dropIfExists('site_texts');
    }
};
