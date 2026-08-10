<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'vendor_id', 'rep_agency_id', 'name', 'email', 'phone', 'position', 'department', 'is_primary'
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function repAgency(): BelongsTo
    {
        return $this->belongsTo(RepAgency::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
