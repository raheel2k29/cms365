<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessEntity extends Model
{
    protected $fillable = ['name', 'code', 'email', 'phone', 'address', 'logo_path', 'is_active'];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
