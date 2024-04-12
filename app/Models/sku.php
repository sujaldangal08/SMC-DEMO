<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sku extends Model
{
    use HasFactory;

    protected $fillable = [
        'SKU',
        'name',
        'barcode',
        'tags',
        'status',
    ];


    public function inventory()
{
    return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
}

public function warehouses()
{
    return $this->belongsToMany(Warehouse::class, 'sku_warehouse', 'sku_id', 'warehouse_id');
}
}
