<?php

namespace App\Models\Xero;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroConnect extends Model
{
    use HasFactory;

    protected $fillable = ['id_token', 'access_token', 'expires_in', 'token_type', 'refresh_token', 'scope'];

    public function tenants()
    {
        return $this->hasMany(XeroTenant::class);
    }
}
