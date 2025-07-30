<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'brand',
        'model',
        'year',
        'price',
        'mileage',
        'fuel_type',
        'transmission',
        'engine_size',
        'color',
        'description',
        'specifications',
        'features',
        'vin',
        'condition',
        'is_featured',
        'is_available',
        'slug'
    ];

    protected $casts = [
        'specifications' => 'array',
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'price' => 'decimal:2',
        'mileage' => 'integer'
    ];

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(VehicleImage::class)->where('is_primary', true);
    }

    public function getFormattedPriceAttribute()
    {
        return '€' . number_format((float) $this->price, 0, ',', '.');
    }

    public function getFormattedMileageAttribute()
    {
        return number_format((int) $this->mileage, 0, ',', '.');
    }

    public function getFullNameAttribute()
    {
        return "{$this->brand} {$this->model} ({$this->year})";
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            if (empty($vehicle->slug)) {
                $vehicle->slug = Str::slug($vehicle->title);
            }
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeByPriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}
