<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insurance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'insurance_type',
        'provider',
        'amount',
        'start_date',
        'end_date',
        'purchase_date',
        'attachment',
        'contact_meta',
    ];

    protected function casts(): array
    {
        return [
            'contact_meta' => 'array',
            'attachment' => 'array',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function getAttachmentAttribute($value)
    {
        return $value ? asset('storage/'.$value) : null;
    }
}
