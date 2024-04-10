<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = [
        'SKU',
        'name',
        'thumbnail_image',
        'description',
        'material_type',
        'stock',
        'cost_price',
        'manufacturing',
        'supplier',
        'serial_number',
    ];

    public function warehouse():\illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Warehouse::class);
    }

}
