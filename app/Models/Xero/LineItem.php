<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'line_item_id',
        'description',
        'quantity',
        'unit_amount',
        'item_code',
        'account_code',
        'tax_type',
        'tax_amount',
        'line_amount',
        'account_id',
        'item_id',
        'tracking_id',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function tracking()
    {
        return $this->belongsTo(Tracking::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}
