<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    public function contact() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
    public function line_items():\Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LineItem::class);
    }
}
