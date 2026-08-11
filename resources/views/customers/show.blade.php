@extends('layouts.app')
@section('title', $customer->name)
@section('page-title', 'Company Profile')

@section('topbar-actions')
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
    <a href="{{ route('contacts.create', ['company_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
        + Add Contact
    </a>
    <a href="{{ route('quotes.create', ['company_id' => $customer->id]) }}" class="btn btn-primary btn-sm">
        + New Quote
    </a>
@endsection

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="detail-grid">
    {{-- Left column: Details --}}
    <div>
        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-avatar">{{ strtoupper(substr($customer->name, 0, 2)) }}</div>
                <div>
                    <div class="detail-name">
                        {{ $customer->name }}
                        @if(!$customer->is_active)
                            <span style="background:var(--danger-soft);color:var(--danger);font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:6px;vertical-align:middle">INACTIVE</span>
                        @endif
                    </div>
                    <div class="detail-sub">{{ $customer->industry ?? 'No industry specified' }}</div>
                </div>
            </div>
            <div class="detail-body">
                <div class="form-row">
                    <div class="detail-field">
                        <div class="detail-field-label">Company Code</div>
                        <div class="detail-field-value">{{ $customer->code ?? '—' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-field-label">Phone</div>
                        <div class="detail-field-value">{{ $customer->phone ?? '—' }}</div>
                    </div>
                </div>
                    <div class="detail-field">
                        <div class="detail-field-label">Website</div>
                        <div class="detail-field-value">
                            @if($customer->website)
                                <a href="{{ str_starts_with($customer->website, 'http') ? $customer->website : 'https://'.$customer->website }}" target="_blank" style="color:var(--accent);text-decoration:none">{{ $customer->website }}</a>
                            @else — @endif
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="detail-field">
                        <div class="detail-field-label">Country</div>
                        <div class="detail-field-value">{{ $customer->country ?? '—' }}</div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-field-label">Address</div>
                        <div class="detail-field-value">{{ $customer->address ?? '—' }}</div>
                    </div>
                </div>
                @if($customer->notes)
                <div class="detail-field">
                    <div class="detail-field-label">Notes</div>
                    <div class="detail-field-value" style="white-space:pre-wrap;color:var(--text-secondary)">{{ $customer->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column: Related data --}}
    <div style="display:flex;flex-direction:column;gap:20px">
        
        {{-- Contacts --}}
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Contacts <span class="list-count">({{ $customer->contacts_count }})</span></div>
            </div>
            @if($customer->contacts->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No contacts added yet.</div>
            @else
                <ul style="list-style:none">
                    @foreach($customer->contacts as $contact)
                    <li style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                        <div>
                            <a href="{{ route('contacts.show', $contact) }}" style="color:var(--text-primary);font-weight:600;text-decoration:none;font-size:13.5px">{{ $contact->name }}</a>
                            @if($contact->is_primary)
                                <span style="background:var(--accent-soft);color:var(--accent);font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:4px;vertical-align:middle">PRIMARY</span>
                            @endif
                            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">{{ $contact->position ?? 'No title' }}</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                                {{ $contact->email ?? 'No email' }} • {{ $contact->phone ?? 'No phone' }}
                            </div>
                        </div>
                        <div class="row-actions">
                            <a href="{{ route('contacts.edit', $contact) }}" class="btn-icon" title="Edit"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Open Pipeline --}}
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Open Pipeline <span class="list-count">({{ $openQuotes->count() }})</span></div>
            </div>
            @if($openQuotes->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No open projects.</div>
            @else
                <ul style="list-style:none">
                    @foreach($openQuotes as $quote)
                    <li style="padding:14px 20px;border-bottom:1px solid var(--border)">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                            <div>
                                <a href="{{ route('quotes.show', $quote) }}" style="color:var(--accent);font-weight:600;text-decoration:none;font-size:13.5px">{{ $quote->quote_number }}</a>
                                <div style="font-size:13px;color:var(--text-primary);margin-top:2px">{{ $quote->project_name ?? 'No project name' }}</div>
                            </div>
                            <div style="text-align:right;font-size:13px;font-weight:600;">
                                ${{ number_format($quote->total_sell, 2) }}
                            </div>
                        </div>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <select class="form-control pipeline-update" data-id="{{ $quote->id }}" data-field="status" style="width:140px;font-size:12px;padding:4px 8px;height:auto;">
                                @php $statuses = ['new', 'in_review', 'rfq_sent', 'pricing_received', 'quote_prepared', 'quote_sent', 'submitted']; @endphp
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" {{ $quote->status == $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                                @endforeach
                            </select>
                            
                            <input type="date" class="form-control pipeline-update" data-id="{{ $quote->id }}" data-field="due_at" 
                                   value="{{ $quote->due_at ? $quote->due_at->format('Y-m-d') : '' }}" 
                                   style="width:130px;font-size:12px;padding:4px 8px;height:auto;">
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Closed Projects --}}
        @if($closedQuotes->isNotEmpty())
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Closed Projects <span class="list-count">({{ $closedQuotes->count() }})</span></div>
            </div>
            <ul style="list-style:none">
                @foreach($closedQuotes as $quote)
                <li style="padding:14px 20px;border-bottom:1px solid var(--border)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <a href="{{ route('quotes.show', $quote) }}" style="color:var(--accent);font-weight:600;text-decoration:none;font-size:13.5px">{{ $quote->quote_number }}</a>
                        <span class="badge badge-{{ $quote->status }}">{{ ucfirst(str_replace('_',' ',$quote->status)) }}</span>
                    </div>
                    <div style="font-size:13px;color:var(--text-primary);margin-top:4px">{{ $quote->project_name ?? 'No project name' }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
                        ${{ number_format($quote->total_sell, 0) }} • {{ $quote->updated_at->format('M d, Y') }}
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.pipeline-update').forEach(el => {
        el.addEventListener('change', function() {
            const quoteId = this.dataset.id;
            const field = this.dataset.field;
            const value = this.value;
            
            // Visual feedback
            this.style.opacity = '0.5';
            
            fetch(`/quotes/${quoteId}/quick-update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    [field]: value
                })
            })
            .then(res => res.json())
            .then(data => {
                this.style.opacity = '1';
                if(!data.success) {
                    alert('Failed to update. Please refresh.');
                }
            })
            .catch(err => {
                this.style.opacity = '1';
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>
@endpush
