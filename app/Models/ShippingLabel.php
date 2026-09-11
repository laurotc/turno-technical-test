<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ShippingLabel extends Model
{
    protected $fillable = [
        'user_id',
        'easypost_shipment_id',
        'easypost_postage_label_id',
        'easypost_rate_id',
        'carrier',
        'service',
        'tracking_code',
        'label_url',
        'label_pdf_url',
        'from_address',
        'to_address',
        'parcel',
        'rate',
        'raw_response',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'from_address' => 'array',
            'to_address' => 'array',
            'parcel' => 'array',
            'rate' => 'array',
            'raw_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
