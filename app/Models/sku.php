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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'SKU');
    }

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }
}
