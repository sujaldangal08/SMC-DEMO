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
        'branch_id',
    ];

    protected $casts = [
        'materials' => 'array',
        'amount' => 'array',
        'rate' => 'array',
        'attachment' => 'array',
        'weighing_type' => 'array',

    ];

    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class);
    }

    public function getAttachmentAttribute($value)
    {
        if ($value) {
            $attachment = json_decode($value);
            if (is_array($attachment)) {
                $attachmentUrl = [];
                foreach ($attachment as $item) {
                    // Ensure the item is a non-empty string before adding it to the URLs array
                    if (is_string($item) && !empty($item)) {
                        $attachmentUrl[] = url($item);
                    }
                }
                return $attachmentUrl;
            }
        }

        return null; // Return null if the image attribute is empty or invalid
    }
}
