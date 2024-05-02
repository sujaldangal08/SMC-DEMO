<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'branch_name',
        'branch_street',
        'branch_street2',
        'branch_city',
        'branch_state',
        'branch_zip',
        'branch_phone',
        'branch_email',
        'branch_code',
        'branch_status',
        'branch_country_id',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
