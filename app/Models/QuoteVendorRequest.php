<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteVendorRequest extends Model
{
    protected $fillable = [
        'quote_id', 'vendor_id', 'status', 'quoted_price', 'requested_at', 'received_at', 'notes'
    ];

    public function quote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
