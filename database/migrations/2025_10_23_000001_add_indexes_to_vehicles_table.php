<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Indexes to speed up catalog queries, especially Vendidos (status=sold)
            if (!Schema::hasColumn('vehicles', 'status')) {
                return; // table not ready yet; skip
            }
            $table->index('status', 'vehicles_status_index');
            $table->index(['status', 'created_at'], 'vehicles_status_created_at_index');
            $table->index('created_at', 'vehicles_created_at_index');
            // Common filter fields
            if (Schema::hasColumn('vehicles', 'brand')) $table->index('brand', 'vehicles_brand_index');
            if (Schema::hasColumn('vehicles', 'model')) $table->index('model', 'vehicles_model_index');
            if (Schema::hasColumn('vehicles', 'year')) $table->index('year', 'vehicles_year_index');
            if (Schema::hasColumn('vehicles', 'price')) $table->index('price', 'vehicles_price_index');
            if (Schema::hasColumn('vehicles', 'offer_price')) $table->index('offer_price', 'vehicles_offer_price_index');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $dropIfExists = function ($table, $index) {
                try { $table->dropIndex($index); } catch (\Throwable $e) { /* ignore */ }
            };
            $dropIfExists($table, 'vehicles_status_index');
            $dropIfExists($table, 'vehicles_status_created_at_index');
            $dropIfExists($table, 'vehicles_created_at_index');
            $dropIfExists($table, 'vehicles_brand_index');
            $dropIfExists($table, 'vehicles_model_index');
            $dropIfExists($table, 'vehicles_year_index');
            $dropIfExists($table, 'vehicles_price_index');
            $dropIfExists($table, 'vehicles_offer_price_index');
        });
    }
};


