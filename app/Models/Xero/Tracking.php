<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_id',
        'name',
        'option',
        'status',
    ];

    public function lineItems() 
    {
        return $this->hasOne(LineItem::class);
    }
}
