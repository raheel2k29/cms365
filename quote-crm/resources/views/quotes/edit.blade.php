@extends('layouts.app')
@section('title', 'Quote Builder')
@section('page-title', 'Quote Builder: ' . $quote->quote_number)
@section('topbar-actions')
<a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-ghost" style="margin-right:8px" target="_blank"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Download PDF</a>
<a href="{{ route('quotes.show', $quote) }}" class="btn btn-ghost">Cancel</a>
<button type="button" class="btn btn-primary" onclick="document.getElementById('quote-form').submit()">Save Quote</button>
@endsection

@push('styles')
<style>
    .builder-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
    .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; font-weight: 700; color: var(--text-primary); }
    .card-body { padding: 20px; }
    
    .item-row { display: grid; grid-template-columns: 1fr 80px 110px 110px 110px 40px; gap: 12px; align-items: end; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed var(--border); }
    .item-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    
    .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13.5px; }
    .summary-label { color: var(--text-secondary); }
    .summary-val { font-weight: 600; color: var(--text-primary); }
    .summary-total { font-size: 16px; font-weight: 700; border-top: 1px solid var(--border); padding-top: 12px; margin-top: 4px; }
    .commission-highlight { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 8px; margin-top: 16px; }
</style>
@endpush

@section('content')
<div class="builder-grid" x-data="quoteBuilder()">
    <!-- Left Column: Line Items & Details -->
    <div>
        <form id="quote-form" action="{{ route('quotes.update', $quote) }}" method="POST">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 13px; border: 1px solid #f87171;">
                    <strong>Please fix the following errors to save the quote:</strong>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="card" style="margin-bottom:24px">
                <div class="card-header">Quote Metadata</div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                        <div>
                            <label class="form-label">Project Name</label>
                            <input type="text" name="project_name" class="form-control" value="{{ old('project_name', $quote->project_name) }}">
                        </div>
                        <div>
                            <label class="form-label">Project Address</label>
                            <input type="text" name="project_address" class="form-control" value="{{ old('project_address', $quote->project_address) }}">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">
                        <div>
                            <label class="form-label">Due Date and Time</label>
                            <input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at', $quote->due_at ? $quote->due_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div>
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $quote->expires_at ? $quote->expires_at->format('Y-m-d') : '') }}">
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                @php $statuses = ['new', 'in_review', 'rfq_sent', 'pricing_received', 'quote_prepared', 'quote_sent', 'won', 'lost', 'cancelled']; @endphp
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" {{ $quote->status == $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Line Items
                    <button type="button" @click="addItem()" class="btn btn-ghost btn-sm" style="font-size:11px">+ Add Item</button>
                </div>
                <div class="card-body">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="item-card" style="border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:16px; background:#fafafa;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <div style="font-weight:600; font-size:14px;">Line Item</div>
                                <button x-show="items.length > 1" type="button" @click="removeItem(item.id)" class="btn btn-ghost btn-sm" style="color:#dc2626">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Remove
                                </button>
                            </div>
                            
                            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Description</label>
                                    <input type="text" x-model="item.description" :name="`items[${index}][description]`" class="form-control" placeholder="Item description">
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Qty</label>
                                    <input type="number" step="0.01" min="0.01" x-model.number="item.qty" :name="`items[${index}][qty]`" class="form-control" required>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">UOM</label>
                                    <input type="text" x-model="item.unit" :name="`items[${index}][unit]`" class="form-control" placeholder="e.g. EA">
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Type</label>
                                    <input type="text" x-model="item.type" :name="`items[${index}][type]`" class="form-control" placeholder="e.g. Labor">
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Quoted By</label>
                                    <input type="text" x-model="item.quoted_by" :name="`items[${index}][quoted_by]`" class="form-control" placeholder="e.g. John Doe">
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Rep</label>
                                    <input type="text" x-model="item.rep" :name="`items[${index}][rep]`" class="form-control">
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Vendor</label>
                                    <select x-model="item.vendor_id" :name="`items[${index}][vendor_id]`" class="form-control">
                                        <option value="">-- None --</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Unit Cost ($)</label>
                                    <input type="number" step="0.01" min="0" x-model.number="item.cost_price" :name="`items[${index}][cost_price]`" class="form-control" required>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Unit Sell ($)</label>
                                    <input type="number" step="0.01" min="0" x-model.number="item.sell_price" :name="`items[${index}][sell_price]`" class="form-control" required>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:var(--text-muted);font-weight:600">Line Total ($)</label>
                                    <div class="form-control" style="background:#f8fafc;color:var(--text-secondary)" x-text="formatMoney(item.qty * item.sell_price)"></div>
                                </div>
                            </div>
                            
                            <div>
                                <label style="font-size:11px;color:var(--text-muted);font-weight:600">Line Note</label>
                                <textarea x-model="item.line_note" :name="`items[${index}][line_note]`" class="form-control" rows="2" placeholder="Optional line note"></textarea>
                            </div>
                        </div>
                    </template>
                    
                    <div x-cloak x-show="items.length === 0" style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;border:1px dashed var(--border);border-radius:8px">
                        No items added yet. Click "+ Add Item" to start building this quote.
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Financial Summary -->
    <div>
        <div class="card" style="position:sticky;top:84px">
            <div class="card-header">Financial Summary</div>
            <div class="card-body">
                <div class="summary-row">
                    <span class="summary-label">Total Cost</span>
                    <span class="summary-val" x-text="formatMoney(totalCost)"></span>
                </div>
                <div class="summary-row summary-total">
                    <span class="summary-label">Total Sell Price</span>
                    <span class="summary-val" x-text="formatMoney(totalSell)"></span>
                </div>
                <div class="summary-row" style="margin-top:12px;padding-top:12px;border-top:1px dashed var(--border)">
                    <span class="summary-label">Gross Profit</span>
                    <span class="summary-val" :style="grossMarginAmount < 0 ? 'color:#dc2626' : ''" x-text="formatMoney(grossMarginAmount)"></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Margin %</span>
                    <span class="summary-val" :style="grossMarginPct < 0 ? 'color:#dc2626' : ''" x-text="grossMarginPct.toFixed(2) + '%'"></span>
                </div>
                
                <div class="commission-highlight">
                    <div style="font-size:11px;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px">Sales Commission (30% GP)</div>
                    <div style="font-size:24px;font-weight:700;color:#15803d" x-text="formatMoney(commission)"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('quoteBuilder', () => ({
        items: [],
        
        init() {
            // Load existing items
            let oldItems = {!! json_encode(old('items')) !!};
            let existing = {!! json_encode($quote->items) !!};
            
            let itemsToLoad = (oldItems && Object.keys(oldItems).length > 0) ? Object.values(oldItems) : existing;
            
            if (itemsToLoad && itemsToLoad.length > 0) {
                this.items = itemsToLoad.map(item => ({
                    id: item.id || 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                    description: item.description || '',
                    qty: parseFloat(item.qty) || 1,
                    cost_price: parseFloat(item.cost_price) || 0,
                    sell_price: parseFloat(item.sell_price) || 0,
                    unit: item.unit || '',
                    type: item.type || '',
                    quoted_by: item.quoted_by || '',
                    rep: item.rep || '',
                    vendor_id: item.vendor_id || '',
                    line_note: item.line_note || ''
                }));
            } else {
                this.addItem();
            }
        },
        
        addItem() {
            this.items.push({
                id: 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                description: '',
                qty: 1,
                cost_price: 0,
                sell_price: 0,
                unit: '',
                type: '',
                quoted_by: '',
                rep: '',
                vendor_id: '',
                line_note: ''
            });
        },
        
        removeItem(id) {
            this.items = this.items.filter(item => item.id !== id);
        },
        
        get totalCost() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.cost_price) || 0)), 0);
        },
        
        get totalSell() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.sell_price) || 0)), 0);
        },
        
        get grossMarginAmount() {
            return this.totalSell - this.totalCost;
        },
        
        get grossMarginPct() {
            return this.totalSell > 0 ? (this.grossMarginAmount / this.totalSell) * 100 : 0;
        },
        
        get commission() {
            return this.grossMarginAmount > 0 ? this.grossMarginAmount * 0.30 : 0;
        },
        
        formatMoney(amount) {
            let num = parseFloat(amount) || 0;
            let sign = num < 0 ? '-' : '';
            return sign + '$' + Math.abs(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }));
});
</script>
@endpush
