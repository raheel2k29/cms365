@extends('layouts.app')
@section('title', 'Pipeline')
@section('page-title', 'Pipeline')

@push('styles')
<style>
    .metrics-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    .metric-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        box-shadow: var(--shadow-sm);
    }
    .metric-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .metric-val {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    .toolbar-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 16px;
    }
    .search-bar {
        flex: 1;
        display: flex;
    }
    .search-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
    }
    .toolbar-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .stage-select {
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 13px;
        background: #fff;
        min-width: 150px;
    }
    .btn-export {
        border: 1px solid var(--border);
        background: #fff;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        color: var(--text-primary);
    }
    
    .company-group {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .company-header {
        background: #fff;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .company-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .company-subtitle {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }
    .company-total {
        font-size: 15px;
        font-weight: 700;
        color: var(--success);
    }
    
    .pipeline-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .pipeline-table th {
        background: #f8fafc;
        text-align: left;
        padding: 12px 20px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--border);
    }
    .pipeline-table td {
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-light);
        vertical-align: middle;
    }
    .pipeline-table tr:last-child td {
        border-bottom: none;
    }
    
    .inline-input {
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 6px 10px;
        font-size: 12px;
        width: 100%;
        background: #fff;
    }
    .inline-input:focus {
        border-color: var(--accent);
        outline: none;
    }
    .prob-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .prob-input {
        width: 60px;
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="metrics-row">
    <div class="metric-card">
        <div class="metric-label">Open Pipeline</div>
        <div class="metric-val">${{ number_format($openPipelineValue, 2) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Weighted Pipeline</div>
        <div class="metric-val" id="global-weighted">${{ number_format($weightedPipelineValue, 2) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Awarded</div>
        <div class="metric-val">${{ number_format($awardedValue, 2) }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Customers</div>
        <div class="metric-val">{{ $customersCount }}</div>
    </div>
</div>

<form method="GET" action="{{ route('pipeline.index') }}" class="toolbar-row">
    <div class="search-bar">
        <input type="text" name="search" class="search-input" placeholder="Customer, project, quote #..." value="{{ request('search') }}">
    </div>
    <div class="toolbar-actions">
        <div style="font-size: 11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Stage</div>
        <select name="stage" class="stage-select" onchange="this.form.submit()">
            <option value="all">All stages</option>
            @foreach($statuses as $st => $label)
                <option value="{{ $st }}" {{ request('stage') == $st ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="button" class="btn-export">Export Pipeline CSV</button>
    </div>
</form>

<div style="font-size: 12px; font-weight:600; color:var(--text-secondary); margin-bottom: 12px;">
    {{ $groupedQuotes->flatten()->count() }} opportunities across {{ $groupedQuotes->count() }} customers
</div>

@foreach($groupedQuotes as $companyName => $quotes)
    @php
        $companyTotal = $quotes->sum('total_sell');
        $companyWeighted = $quotes->sum(function($q) {
            return ($q->total_sell * ($q->probability ?? 0)) / 100;
        });
    @endphp
    <div class="company-group">
        <div class="company-header">
            <div>
                <div class="company-title">{{ $companyName }}</div>
                <div class="company-subtitle">{{ $quotes->count() }} opportunities • Weighted $<span id="comp-weight-{{ Str::slug($companyName) }}">{{ number_format($companyWeighted, 2) }}</span></div>
            </div>
            <div class="company-total">${{ number_format($companyTotal, 2) }}</div>
        </div>
        <table class="pipeline-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Project / Quote</th>
                    <th style="width: 15%;">Stage</th>
                    <th style="width: 10%;">Probability</th>
                    <th style="width: 15%;">Expected Close</th>
                    <th style="width: 10%; text-align:right;">Quote Value</th>
                    <th style="width: 10%; text-align:right;">Weighted</th>
                    <th style="width: 15%;">Next Step / Notes</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotes as $quote)
                @php
                    $weighted = ($quote->total_sell * ($quote->probability ?? 0)) / 100;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 500; color:var(--text-primary);">{{ $quote->project_name ?? 'Untitled Project' }}</div>
                        <div style="color:var(--text-muted); font-size:11.5px; margin-top:2px;">{{ $quote->quote_number }}</div>
                    </td>
                    <td>
                        <select class="inline-input update-field" data-id="{{ $quote->id }}" data-field="status">
                            @foreach($statuses as $st => $label)
                                <option value="{{ $st }}" {{ $quote->status == $st ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="prob-wrapper">
                            <input type="number" class="inline-input prob-input update-field" data-id="{{ $quote->id }}" data-field="probability" value="{{ $quote->probability }}" min="0" max="100" data-value="{{ $quote->total_sell }}" data-comp="{{ Str::slug($companyName) }}">
                            <span>%</span>
                        </div>
                    </td>
                    <td>
                        <input type="date" class="inline-input update-field" data-id="{{ $quote->id }}" data-field="expected_close" value="{{ $quote->expected_close ? $quote->expected_close->format('Y-m-d') : '' }}">
                    </td>
                    <td style="text-align:right; font-weight: 600; color:var(--text-primary);">
                        ${{ number_format($quote->total_sell, 2) }}
                    </td>
                    <td style="text-align:right; font-weight: 600; color:var(--text-primary);" id="row-weight-{{ $quote->id }}">
                        ${{ number_format($weighted, 2) }}
                    </td>
                    <td>
                        <input type="text" class="inline-input update-field" data-id="{{ $quote->id }}" data-field="next_step" value="{{ $quote->next_step }}" placeholder="Follow-up or next step">
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ route('quotes.show', $quote) }}" class="btn-export" style="padding: 4px 8px; font-size: 11px; text-decoration:none;">Open Quote</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    let debounceTimer;
    
    document.querySelectorAll('.update-field').forEach(el => {
        el.addEventListener('change', function() {
            saveField(this);
        });
        
        // For text inputs, auto-save while typing (debounced)
        if (el.type === 'text') {
            el.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    saveField(this);
                }, 1000);
            });
        }
    });

    function saveField(element) {
        const quoteId = element.dataset.id;
        const field = element.dataset.field;
        const value = element.value;
        
        // Optimistic UI updates for Probability
        if (field === 'probability') {
            let prob = parseFloat(value) || 0;
            let val = parseFloat(element.dataset.value) || 0;
            let newWeighted = (val * prob) / 100;
            
            // Update row weighted text (simple JS format)
            document.getElementById(`row-weight-${quoteId}`).innerText = '$' + newWeighted.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // We should ideally recount the entire company and global totals via JS or reload,
            // but for now, we just visually indicate it's saved. A full reload is better for strict accuracy.
        }

        element.style.opacity = '0.5';
        
        fetch(`{{ route('pipeline.quick-update') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                quote_id: quoteId,
                field: field,
                value: value
            })
        })
        .then(res => res.json())
        .then(data => {
            element.style.opacity = '1';
            if(!data.success) {
                alert('Failed to update.');
            } else if (field === 'probability' || field === 'status') {
                // If probability or status changes, totals change. Refresh to ensure 100% accuracy on top metrics.
                window.location.reload(); 
            }
        })
        .catch(err => {
            element.style.opacity = '1';
            alert('An error occurred.');
        });
    }
});
</script>
@endpush
