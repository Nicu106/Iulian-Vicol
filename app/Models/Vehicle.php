<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug','brand','model','title','year','price','original_price','offer_price','has_offer','offer_expires_at',
        'offer_type','offer_description','pricing_history','status','sold_date','buyer_name','buyer_phone','featured','priority','badges',
        'mileage','fuel','transmission','engine','power','drivetrain','color','vin','condition',
        'description','features','video_url','cover_image','gallery_images',
        'purchase_price','internal_notes','views_count','inquiries_count',
        'location','availability_schedule','meta_title','meta_description','tags',
        'fuel_type','body_type','engine_capacity','images','seller_name','seller_phone','seller_email'
    ];

    protected $casts = [
        'features' => 'array',
        'gallery_images' => 'array',
        'badges' => 'array',
        'availability_schedule' => 'array',
        'tags' => 'array',
        'pricing_history' => 'array',
        'images' => 'array',
        'year' => 'integer',
        'mileage' => 'integer',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'has_offer' => 'boolean',
        'featured' => 'boolean',
        'priority' => 'integer',
        'views_count' => 'integer',
        'inquiries_count' => 'integer',
        'offer_expires_at' => 'date',
        'sold_date' => 'date',
    ];

    // Scopes for filtering
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeWithOffers(Builder $query): Builder
    {
        return $query->where('has_offer', true)
                    ->where(function($q) {
                        $q->whereNull('offer_expires_at')
                          ->orWhere('offer_expires_at', '>=', now());
                    });
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Accessors & Mutators
    public function getPriceAttribute($value): ?float
    {
        return $value ? (float) $value : null;
    }
    
    public function getOfferPriceAttribute($value): ?float
    {
        return $value ? (float) $value : null;
    }
    
    public function getOriginalPriceAttribute($value): ?float
    {
        return $value ? (float) $value : null;
    }
    
    public function getDisplayPriceAttribute(): ?float
    {
        if ($this->hasActiveOffer) {
            return $this->offer_price;
        }
        return $this->price;
    }

    public function getHasActiveOfferAttribute(): bool
    {
        $notExpired = (!$this->offer_expires_at || $this->offer_expires_at >= now());
        $baseOriginal = $this->original_price ?: $this->price;
        $isCheaper = $this->offer_price && $baseOriginal && ($this->offer_price < $baseOriginal);
        return (bool) ($this->has_offer && $isCheaper && $notExpired);
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->hasActiveOffer) {
            return null;
        }
        $baseOriginal = $this->original_price ?: $this->price;
        if (!$baseOriginal || !$this->offer_price || $this->offer_price >= $baseOriginal) {
            return null;
        }
        return (int) round((($baseOriginal - $this->offer_price) / $baseOriginal) * 100);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    // Helper methods
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementInquiries(): void
    {
        $this->increment('inquiries_count');
    }

    public function markAsSold(?string $buyerName = null, ?string $buyerPhone = null): void
    {
        $this->update([
            'status' => 'sold',
            'sold_date' => now(),
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
        ]);
    }

    public function toggleFeatured(): void
    {
        $this->update(['featured' => !$this->featured]);
    }
}