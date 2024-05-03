<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdHoc extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'staff_id',
        'materials',
        'rate',
        'staff_status',
        'weighing_type',
        'notes',
        'amount',
        'customer_status',
        'attachment',
    ];

    protected $casts = [
        'materials' => 'array',
        'amount' => 'array',
        'rate' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class);
    }
}
