<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteStatus extends Model
{
    use HasFactory;

    protected $fillable = ['business_entity_id', 'name', 'color', 'order_index'];

    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
