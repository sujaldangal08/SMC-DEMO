<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rego_number',
        'driver_id',
        'customer_id',
        'route_id',
        'material',
        'full_bin_weight',
        'next_truck_weight',
        'tare_bin',
        'gross_weight',
        'notes',
        'image',
        'weighing_type',
        'ticked_type',
        'lot_number',
        'ticket_number',
        'in_time',
        'out_time',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function wastes()
    {
        return $this->hasOne(Waste::class);
    }

    protected function casts(): array
    {
        return [
            'in_time' => 'datetime',
            'out_time' => 'datetime',
            'image' => 'array',
        ];
    }
}
