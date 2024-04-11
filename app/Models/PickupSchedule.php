<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickupSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'driver_id',
        'customer_id',
        'asset_id',
        'pickup_date',
        'status',
        'notes'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
