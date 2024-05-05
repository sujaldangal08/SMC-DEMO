<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'rego_number',
        'asset_type',
        'meta',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'json',
        ];
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function schedules()
    {
        return $this->hasMany(PickupSchedule::class);
    }

    public function getImageAttribute($value)
    {
        return $value ? asset('storage/'.$value) : null;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
