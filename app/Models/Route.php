<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_point',
        'end_point',
        'distance',
        'duration',
        'status'
    ];

    public function schedule()
    {
        return $this->hasMany(PickupSchedule::class);
    }
}
