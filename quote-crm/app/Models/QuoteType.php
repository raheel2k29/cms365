<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteType extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
