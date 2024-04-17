<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waste extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'quantity',
        'image',
        'notes'
    ];

    protected function casts(): array
    {
        return [
            'ticket_id' => 'integer',
            'quantity' => 'integer',
            'image' => 'array',
            'notes' => 'string'
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
