<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;


    protected $primaryKey = 'id';
    protected $fillable = [
        'SKU_id',
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

    public function sku()
{
    return $this->hasOne(Sku::class, 'inventory_id', 'id');
}


}
