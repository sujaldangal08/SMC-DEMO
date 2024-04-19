<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroConnect extends Model
{
    use HasFactory;

    public function tenants()
    {
        return $this->hasMany(XeroTenant::class);
    }
}
