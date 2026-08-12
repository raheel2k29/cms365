<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorItem extends Model
{
    protected $fillable = [
        'vendor_id', 'item_number', 'description', 'cost_price', 'sell_price', 'unit'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
