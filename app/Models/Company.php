<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name',
        'company_street',
        'company_street2',
        'company_city',
        'company_state',
        'company_zip',
        'company_phone',
        'company_email',
        'company_code',
        'company_country_id'
    ];
    public function branches() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
