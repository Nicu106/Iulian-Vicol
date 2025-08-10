<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Pricing & Offers
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
            $table->decimal('offer_price', 10, 2)->nullable()->after('original_price');
            $table->boolean('has_offer')->default(false)->after('offer_price');
            $table->date('offer_expires_at')->nullable()->after('has_offer');
            
            // Status Management
            $table->enum('status', ['available', 'reserved', 'sold', 'maintenance', 'draft'])->default('available')->after('offer_expires_at');
            $table->date('sold_date')->nullable()->after('status');
            $table->string('buyer_name')->nullable()->after('sold_date');
            $table->string('buyer_phone')->nullable()->after('buyer_name');
            
            // Marketing & Display
            $table->boolean('featured')->default(false)->after('buyer_phone');
            $table->integer('priority')->default(0)->after('featured'); // Higher = more important
            $table->json('badges')->nullable()->after('priority'); // ["Nou", "Garanție", "Finanțare"]
            
            // Business Data
            $table->decimal('purchase_price', 10, 2)->nullable()->after('badges'); // Internal cost
            $table->text('internal_notes')->nullable()->after('purchase_price'); // Private notes
            $table->integer('views_count')->default(0)->after('internal_notes');
            $table->integer('inquiries_count')->default(0)->after('views_count');
            
            // Location & Availability
            $table->string('location')->nullable()->after('inquiries_count');
            $table->json('availability_schedule')->nullable()->after('location'); // When available for viewing
            
            // SEO & Meta
            $table->string('meta_title')->nullable()->after('availability_schedule');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->json('tags')->nullable()->after('meta_description'); // ["sport", "family", "luxury"]
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'original_price', 'offer_price', 'has_offer', 'offer_expires_at',
                'status', 'sold_date', 'buyer_name', 'buyer_phone',
                'featured', 'priority', 'badges',
                'purchase_price', 'internal_notes', 'views_count', 'inquiries_count',
                'location', 'availability_schedule',
                'meta_title', 'meta_description', 'tags'
            ]);
        });
    }
};
