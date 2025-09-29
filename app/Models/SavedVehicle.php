<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedVehicle extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_id',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'saved_at' => 'datetime'
    ];

    protected $dates = [
        'saved_at'
    ];

    /**
     * Relația cu utilizatorul
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relația cu mașina
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope pentru a filtra după utilizator
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pentru a filtra după mașină
     */
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}
