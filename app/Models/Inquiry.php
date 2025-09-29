<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'vehicle_slug',
        'vehicle_title',
        'vehicle_link',
        'name',
        'phone',
        'message',
        'status',
    ];
}


