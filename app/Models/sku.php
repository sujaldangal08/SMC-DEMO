<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sku extends Model
{
    use HasFactory;

    protected $fillable = [
        'SKU',
        'inventory_id'
    ];

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'SKU_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'SKU_id');
    }
}
