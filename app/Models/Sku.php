<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'barcode',
        'tags',
        'status',
        'branch_id',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }

    public function warehouses()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($sku) {
            $maxId = Sku::max('id') + 1;
            $sku->SKU = 'SKU'.str_pad($maxId, 3, '0', STR_PAD_LEFT);
        });
    }
}
