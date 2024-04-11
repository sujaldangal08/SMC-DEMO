<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'asset_type',
        'meta'
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array'
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
}
