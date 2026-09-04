<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which photograph shows what.
 *
 * A buyer looking at a used car wants three different things at three different
 * moments: what it looks like, what it is like to sit in, and what is wrong with
 * it. The photographs already exist — up to 58 per car — but nothing says which
 * is which, so they arrive as one undifferentiated pile.
 *
 * Shape: { "<image path>": "exterior" | "interior" | "flaw" }. Anything not
 * listed is untagged and shows only under "Todas", so tagging can be partial and
 * the page still works — which is what will actually happen when a dealer tags
 * the newest car and not the forty behind it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->json('image_tags')->nullable()->after('gallery_images');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('image_tags');
        });
    }
};
