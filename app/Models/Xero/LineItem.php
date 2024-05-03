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
        'discount_rate',
        'discount_amount',
        'total_amount',
    ];
}
