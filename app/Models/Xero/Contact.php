<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function phones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function balances(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Balances::class);
    }

    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
