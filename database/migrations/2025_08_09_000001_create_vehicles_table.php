<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('price');
            $table->string('mileage')->nullable();
            $table->string('fuel')->nullable();
            $table->string('transmission')->nullable();
            $table->string('engine')->nullable();
            $table->string('power')->nullable();
            $table->string('drivetrain')->nullable();
            $table->string('color')->nullable();
            $table->string('vin')->nullable();
            $table->string('condition')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('video_url')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};


