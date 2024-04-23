<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location',
        'SKU_id',
    ];

    protected $casts = [
        'SKU_id' => 'array',
    ];

    public function skus()
    {
        return $this->belongsToMany(Sku::class, 'sku_warehouse', 'warehouse_id', 'sku_id');
    }
}
