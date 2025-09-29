<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, clean the existing price data by removing Euro symbols and spaces
        DB::statement("UPDATE vehicles SET price = REPLACE(REPLACE(price, '€', ''), ' ', '') WHERE price LIKE '%€%' OR price LIKE '% %'");
        
        // Also clean offer_price and original_price if they exist
        if (Schema::hasColumn('vehicles', 'offer_price')) {
            DB::statement("UPDATE vehicles SET offer_price = REPLACE(REPLACE(offer_price, '€', ''), ' ', '') WHERE offer_price LIKE '%€%' OR offer_price LIKE '% %'");
        }
        
        if (Schema::hasColumn('vehicles', 'original_price')) {
            DB::statement("UPDATE vehicles SET original_price = REPLACE(REPLACE(original_price, '€', ''), ' ', '') WHERE original_price LIKE '%€%' OR original_price LIKE '% %'");
        }
        
        // Now change the price field from string to decimal
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Revert price field back to string
            $table->string('price')->change();
        });
    }
};
