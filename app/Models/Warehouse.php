<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    public function skus()
    {
        return $this->belongsToMany(Sku::class, 'sku_warehouse', 'warehouse_id', 'sku_id');
    }

}
