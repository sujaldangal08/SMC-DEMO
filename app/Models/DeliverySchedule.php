<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliverySchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'driver_id',
        'truck_id',
        'coordinates',
        'materials',
        'amount',
        'n_trips',
        'n_trips_done',
        'interval',
        'start_date',
        'end_date',
        'status',
        'delivery_notes',
        'meta'
    ];

    // Using cast to convert the coordinates, materials, and amount to an array for easy manipulation
    protected function casts(): array
    {
        return [
            'coordinates' => 'array',
            'materials' => 'array',
            'amount' => 'array',
            'meta' => 'json'
        ];
    }
}
