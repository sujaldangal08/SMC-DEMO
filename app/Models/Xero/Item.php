<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'code',
        'description',
        'unit_price',
        'unit',
        'tax_type',
        'tax_amount',
        'account_code',
        'tracking_id',
    ];

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function tracking()
    {
        return $this->belongsTo(Tracking::class);
    }

}
