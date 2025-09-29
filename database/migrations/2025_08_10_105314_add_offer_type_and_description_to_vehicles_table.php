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
            // Offer details
            $table->enum('offer_type', ['flash_sale', 'seasonal', 'clearance', 'negotiable', 'promotion'])->nullable()->after('offer_expires_at');
            $table->text('offer_description')->nullable()->after('offer_type');
            
            // Pricing history for tracking changes
            $table->json('pricing_history')->nullable()->after('offer_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'offer_type', 'offer_description', 'pricing_history'
            ]);
        });
    }
};
