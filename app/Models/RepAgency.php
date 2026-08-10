<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepAgency extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'notes'
    ];

    public function vendors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
