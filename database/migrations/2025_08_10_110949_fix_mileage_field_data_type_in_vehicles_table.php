<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, clean the existing mileage data by removing any non-numeric characters
        DB::statement("UPDATE vehicles SET mileage = REPLACE(REPLACE(REPLACE(mileage, 'km', ''), ' ', ''), ',', '') WHERE mileage IS NOT NULL AND mileage != ''");
        
        // For SQLite, we'll use a simpler approach - just try to convert to integer
        // If it fails, it will remain as is, but we'll handle it in the model
        
        // Now change the mileage field from string to integer
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('mileage')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Revert mileage field back to string
            $table->string('mileage')->nullable()->change();
        });
    }
};
