<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A link the owner sends a customer so they write their own opinion
 * (App\Models\ReviewLink). One link, one opinion, open until expires_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();
            $table->string('name', 80)->nullable();          // who it is for; fills their form
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();         // when the opinion came in (and the consent with it)
            $table->foreignId('testimonial_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_links');
    }
};
