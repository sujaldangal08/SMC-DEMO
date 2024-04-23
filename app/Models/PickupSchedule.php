<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickupSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'route_id',
        'driver_id',
        'asset_id',
        'customer_id',
        'pickup_date',
        'status',
        'notes',
        'n_bins',
        'materials',
        'weighing_type',
        'tare_weight',
        'image',
        'coordinates'
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class);
    }


    // protected function casts(): array
    // {
    //     return [
    //         'coordinates' => 'array',
    //         'image' => 'array',
    //         'materials' => 'array',
    //         'weighing_type' => 'array',
    //     ];
    // }
    protected $casts = [
        'coordinates' => 'array',
        'image' => 'array',
        'materials' => 'array',
        'weighing_type' => 'array',
        'tare_weight' => 'array',
    ];
    protected static function booted()
    {
        static::creating(function ($pickupSchedule) {
            if (!isset($pickupSchedule->driver_id) && isset($pickupSchedule->route_id)) {
                $route = Route::find($pickupSchedule->route_id);
                if ($route && $route->driver_id) {
                    $pickupSchedule->driver_id = $route->driver_id;
                }
            }
        });
        static::updating(function ($pickupSchedule) {
            if ($pickupSchedule->isDirty('route_id')) {
                $route = Route::find($pickupSchedule->route_id);
                if ($route) {
                    $pickupSchedule->driver_id = $route->driver_id;
                    $pickupSchedule->asset_id = $route->asset_id;
                }
            }
        });
    }
}
