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
        Schema::create('saved_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable(); // Note personale despre mașina salvată
            $table->json('metadata')->nullable(); // Metadate suplimentare (preferințe, etc.)
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();
            
            // Un utilizator nu poate salva aceeași mașină de două ori
            $table->unique(['user_id', 'vehicle_id']);
            
            // Indexuri pentru performanță
            $table->index(['user_id', 'saved_at']);
            $table->index('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_vehicles');
    }
};
