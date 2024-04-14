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
        'tare_weight',
        'image',
        'coordinates'
    ];

    public function route()
    {
        return $this->hasOne(Route::class);
    }

    public function driver()
    {
        return $this->hasOne(User::class);
    }

    public function asset()
    {
        return $this->hasOne(Asset::class);
    }

    public function customer()
    {
        return $this->hasOne(User::class);
    }


    protected function casts(): array
    {
        return [
            'coordinates' => 'array',
            'image' => 'array'
        ];
    }
}
