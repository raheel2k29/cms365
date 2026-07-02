<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_number', 'business_entity_id', 'quote_type_id', 'contact_id',
        'company_id', 'assigned_to', 'project_name', 'project_address', 'status', 'currency',
        'source', 'quickbooks_ref', 'requested_at', 'due_at', 'expires_at',
        'quote_sent_at', 'won_lost_at', 'lost_reason',
        'total_cost', 'total_sell', 'gross_margin_amount', 'gross_margin_pct', 'commission_amount',
    ];

    protected $casts = [
        'requested_at'  => 'date',
        'due_at'        => 'datetime',
        'expires_at'    => 'date',
        'quote_sent_at' => 'date',
        'won_lost_at'   => 'date',
        'total_cost'    => 'decimal:2',
        'total_sell'    => 'decimal:2',
        'gross_margin_amount' => 'decimal:2',
        'gross_margin_pct'    => 'decimal:2',
        'commission_amount'   => 'decimal:2',
    ];

    public static function generateNumber(): string
    {
        $year   = now()->year;
        $prefix = "QT-{$year}-";
        $last   = static::withTrashed()->where('quote_number', 'like', "{$prefix}%")->count();
        return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function businessEntity(): BelongsTo { return $this->belongsTo(BusinessEntity::class); }
    public function quoteType(): BelongsTo      { return $this->belongsTo(QuoteType::class); }
    public function company(): BelongsTo        { return $this->belongsTo(Company::class); }
    public function contact(): BelongsTo        { return $this->belongsTo(Contact::class); }
    public function assignedUser(): BelongsTo   { return $this->belongsTo(User::class, 'assigned_to'); }
    public function items(): HasMany            { return $this->hasMany(QuoteItem::class)->orderBy('sort_order'); }
    public function vendorRequests(): HasMany   { return $this->hasMany(QuoteVendorRequest::class); }
    public function links(): HasMany            { return $this->hasMany(QuoteLink::class); }
    public function emails(): HasMany           { return $this->hasMany(Email::class); }
    public function attachments(): HasMany      { return $this->hasMany(Attachment::class); }
    public function notes(): HasMany            { return $this->hasMany(Note::class)->orderByDesc('is_pinned')->orderByDesc('created_at'); }
    public function activityLogs(): HasMany     { return $this->hasMany(ActivityLog::class)->orderByDesc('created_at'); }

    public function calculateTotals(): void
    {
        $this->total_cost = $this->items->sum(function($item) {
            return $item->qty * $item->cost_price;
        });
        $this->total_sell = $this->items()->sum('line_total');
        $this->gross_margin_amount = $this->total_sell - $this->total_cost;
        
        if ($this->total_sell > 0) {
            $this->gross_margin_pct = ($this->gross_margin_amount / $this->total_sell) * 100;
        } else {
            $this->gross_margin_pct = 0;
        }

        // Commission is fixed at 30% of Gross Profit
        $this->commission_amount = $this->gross_margin_amount * 0.30;
        $this->save();
    }
}
