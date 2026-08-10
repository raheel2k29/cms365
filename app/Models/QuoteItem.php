<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id', 'sort_order', 'description', 'spec_sheet_url', 'qty', 'unit',
        'cost_price', 'sell_price', 'margin_pct', 'line_total',
        'type', 'rep', 'vendor_id', 'quoted_by', 'line_note'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
