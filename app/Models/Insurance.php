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
        if ($value) {
            $images = json_decode($value);
            if (is_array($images)) {
                $imageUrls = [];
                foreach ($images as $image) {
                    // Ensure the image is a non-empty string before adding it to the URLs array
                    if (is_string($image) && ! empty($image)) {
                        $imageUrls[] = url($image);
                    }
                }

                return $imageUrls;
            }
        }

        return null; // Return null if the image attribute is empty or invalid
    }
}
