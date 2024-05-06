<?php

namespace App\Models;

use App\Models\Xero\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'invoice_id',
        'invoice_number',
        'reference',
        'amount_due',
        'amount_paid',
        'amount_credited',
        'contact_id',
        'status',
        'line_amount_types',
        'currency_code',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
