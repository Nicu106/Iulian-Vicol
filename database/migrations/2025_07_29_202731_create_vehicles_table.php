<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->decimal('price', 10, 2);
            $table->string('mileage');
            $table->string('fuel_type');
            $table->string('transmission');
            $table->string('engine_size');
            $table->string('color');
            $table->text('description');
            $table->json('specifications')->nullable();
            $table->json('features')->nullable();
            $table->string('vin')->nullable();
            $table->string('condition');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
