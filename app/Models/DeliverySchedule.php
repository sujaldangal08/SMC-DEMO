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

    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function truck()
    {
        return $this->belongsTo(Asset::class);
    }

    public function deliveryTrips()
    {
        return $this->hasMany(DeliveryTrip::class);
    }

    // This creates a new delivery trip when a delivery schedule is created and assign the related details
    protected static function booted()
    {
        static::created(function ($deliverySchedule) {
            $deliveryTrip = new DeliveryTrip();
            $deliveryTrip->schedule_id = $deliverySchedule->id;
            // These are assigned as they can be changed
            $deliveryTrip->driver_id = $deliverySchedule->driver_id;
            $deliveryTrip->truck_id = $deliverySchedule->truck_id;
            $deliveryTrip->materials_loaded = $deliverySchedule->materials;
            $deliveryTrip->amount_loaded = $deliverySchedule->amount;
            $deliveryTrip->trip_number = 1;
            $deliveryTrip->status = 'pending';
            $deliveryTrip->trip_date = $deliverySchedule->start_date; // Assign the start date as the trip date
            // Save the delivery trip
            $deliveryTrip->save();
        });
    }

    // Define an accessor 
    public function getIsCompletedAttribute(): bool
    {
        //Get the data related to the schedule and count the number of trips assigned
        $deliveryTripsCount = $this->deliveryTrips()->count();
        //Check if the number of trips assigned is equal to the total number of trips
        if ($deliveryTripsCount == $this->n_trips) {
            return true;
        }
        return false;
    }

    // Define an accessor to get the delivery date based on the interval
    public function getDeliveryDateAttribute(): array
    {
        $deliveryDates = [];
        // Loop through the number of trips
        for ($i = 0; $i < $this->n_trips; $i++) {
            // Calculate the delivery date based on the interval
            $deliveryDate = date('Y-m-d', strtotime($this->start_date . ' + ' . ($i * $this->interval) . ' days'));
            $deliveryDates[] = $deliveryDate;
        }
        return $deliveryDates;
    }

    public function createDeliveryTrip(): void
    {
        // Get the last delivery trip
        $lastDeliveryTrip = $this->deliveryTrips()->orderBy('trip_number', 'desc')->first();

        // Create a new delivery trip
        $deliveryTrip = new DeliveryTrip();
        $deliveryTrip->schedule_id = $this->id;
        $deliveryTrip->driver_id = $this->driver_id;
        $deliveryTrip->truck_id = $this->truck_id;
        $deliveryTrip->materials_loaded = $this->materials;
        $deliveryTrip->amount_loaded = $this->amount;
        $deliveryTrip->trip_number = $lastDeliveryTrip->trip_number + 1;
        $deliveryTrip->status = 'pending';
        // Calculate the trip date based on the interval of the last trip
        $deliveryTrip->trip_date = date('Y-m-d', strtotime($lastDeliveryTrip->trip_date . ' + ' . $this->interval . ' days'));
        $deliveryTrip->save();
    }
}
