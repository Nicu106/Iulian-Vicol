<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* The car's sheet from coches.net (App\Support\CochesNetSheet): the facts the form has
   no field for (doors, seats, boot, CO2, DGT label, warranty), its past (price new in
   Spain, the factory extras with their prices, what the description says) and where it
   came from (stock id, the PDF kept under storage/app/sheets). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->json('sheet')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('sheet');
        });
    }
};
