<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'logo',
        'top_link',
        'top_text',
        'title',
        'emessage',
        'icon',
        'buttons',
        'button_link',
        'footer_address',
        'footer_message',
        'footer_link',
        'footer_text',
        'color',
        'template_type',
    ];
}
