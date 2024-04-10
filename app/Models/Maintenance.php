<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'contact_meta',
        'service_date',
    ];

    protected function casts(): array
    {
        return [
            'contact_meta' => 'array',
            'attachment' => 'array'
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
