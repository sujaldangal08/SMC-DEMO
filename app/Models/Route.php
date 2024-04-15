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

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($route) {
            if ($route->status === 'full') {
                $newRoute = $route->replicate();
                $newRoute->status = 'pending';
                $newRoute->push();

                foreach ($route->schedule->where('status', '!=', 'done') as $schedule) {
                    $schedule->status = 'changed';
                    $newSchedule = $schedule->replicate();
                    $newSchedule->route_id = $newRoute->id;
                    $newSchedule->status = 'pending';
                    $newSchedule->save();
                }
            }
        });
    }
}
