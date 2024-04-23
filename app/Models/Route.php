<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var \Illuminate\Support\Collection|mixed
     */
    public mixed $customer_names;
    public mixed $total_materials;
    protected $fillable = [
        'start_date',
        'name',
        'description',
        'start_point',
        'end_point',
        'distance',
        'duration',
        'status',
        'driver_id',
        'asset_id',
    ];

    public function schedule()
    {
        return $this->hasMany(PickupSchedule::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id')->through(PickupSchedule::class);
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
                    $schedule->status = ' ';
                    $newSchedule = $schedule->replicate();
                    $newSchedule->route_id = $newRoute->id;
                    $newSchedule->status = 'pending';
                    $newSchedule->save();
                }
            }
        });
        static::updating(function ($route) {
            if ($route->isDirty('driver_id')) {
                foreach ($route->schedule->where('status', '!=', 'done') as $schedule) {
                    $schedule->driver_id = $route->driver_id;
                    $schedule->save();
                }
            }
        });

        static::updating(function ($route) {
            if ($route->isDirty('driver_id')) {
                foreach ($route->schedule->where('status', '!=', 'done') as $schedule) {
                    $schedule->driver_id = $route->driver_id;
                    $schedule->save();
                }
            }
            if ($route->isDirty('asset_id')) {
                foreach ($route->schedule->where('status', '!=', 'done') as $schedule) {
                    $schedule->asset_id = $route->asset_id;
                    $schedule->save();
                }
            }
        });
    }
}
