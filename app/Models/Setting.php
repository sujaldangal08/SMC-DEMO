<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = false; // Since setting_id is not auto-incrementing

    protected $fillable = ['setting_id', 'setting_name', 'setting_value'];

    public function getRouteKeyName()
    {
        return 'id';
    }
}
