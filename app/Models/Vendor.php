<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rep_agency_id', 'name', 'contact_person', 'default_email', 'phone', 'country', 'specialty', 'is_active', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function repAgency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RepAgency::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteVendorRequest::class);
    }
}
