<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'purchase_order_number',
        'date',
        'delivery_date',
        'delivery_address',
        'attention_to',
        'telephone',
        'delivery_instructions',
        'has_errors',
        'is_discounted',
        'reference',
        'type',
        'currency_rate',
        'currency_code',
        'contact_id',
        'branding_theme_id',
        'status',
        'line_amount_types',
        'sub_total',
        'total_tax',
        'total',
        'updated_date_utc',
        'has_attachments',
    ];

    public function contact(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lineItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LineItem::class);
    }
}
