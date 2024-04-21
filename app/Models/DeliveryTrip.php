<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTrip extends Model
{
    use HasFactory;

    // Define the fillable fields
    protected $fillable = [
        'schedule_id',
        'driver_id',
        'truck_id',
        'materials_loaded',
        'amount_loaded',
        'trip_number',
        'status'
    ];

    public function casts(): array
    {
        return [
            'materials_loaded' => 'array',
            'amount_loaded' => 'array'
        ];
    }

    // Define the relationship between the delivery trip and the delivery schedule
    public function schedule()
    {
        return $this->belongsTo(DeliverySchedule::class);
    }

    // Define the relationship between the delivery trip and the driver
    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship between the delivery trip and the truck
    public function truck()
    {
        return $this->belongsTo(Asset::class);
    }
}
