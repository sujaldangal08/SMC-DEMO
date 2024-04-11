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


public function warehouse(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(Warehouse::class);
}

public function inventory(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(Inventory::class);
}

}
