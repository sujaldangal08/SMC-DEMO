<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'truck_id',
        'driver_id',
        'customer_id',
        'delivery_location',
        'delivery_start_date',
        'delivery_end_date',
        'delivery_start_time',
        'delivery_end_time',
        'delivery_file',
        'delivery_interval',
        'delivery_status',
        'delivery_notes'
    ];

    public function user() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class);
    }

    public function asset(){
        return $this->belongsTo(Asset::class);
    }
}
