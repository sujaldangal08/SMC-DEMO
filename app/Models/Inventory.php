<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $primaryKey = 'SKU';
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

    public function sku(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(sku::class);
    }


}
