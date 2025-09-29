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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('fuel_type')->nullable()->after('fuel');
            $table->string('body_type')->nullable()->after('fuel_type');
            $table->integer('engine_capacity')->nullable()->after('body_type');
            $table->json('images')->nullable()->after('gallery_images');
            $table->string('seller_name')->nullable()->after('images');
            $table->string('seller_phone')->nullable()->after('seller_name');
            $table->string('seller_email')->nullable()->after('seller_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'fuel_type',
                'body_type', 
                'engine_capacity',
                'images',
                'seller_name',
                'seller_phone',
                'seller_email'
            ]);
        });
    }
};
