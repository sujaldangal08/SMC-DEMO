<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroTenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'connection_id',
        'authEventId',
        'tenantId',
        'tenantType',
        'tenantName',
        'xero_connect_id',
        'createdDateUtc',
        'updatedDateUtc',
    ];

    public function tenants()
    {
        return $this->hasMany(XeroTenant::class);
    }
}
