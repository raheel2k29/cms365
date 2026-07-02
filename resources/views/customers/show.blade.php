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

        {{-- Recent Quotes --}}
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Recent Quotes <span class="list-count">({{ $customer->quotes_count }})</span></div>
            </div>
            @if($customer->quotes->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No quotes yet.</div>
            @else
                <ul style="list-style:none">
                    @foreach($customer->quotes as $quote)
                    <li style="padding:14px 20px;border-bottom:1px solid var(--border)">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <a href="{{ route('quotes.show', $quote) }}" style="color:var(--accent);font-weight:600;text-decoration:none;font-size:13.5px">{{ $quote->quote_number }}</a>
                            <span class="badge badge-{{ $quote->status }}">{{ ucfirst(str_replace('_',' ',$quote->status)) }}</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-primary);margin-top:4px">{{ $quote->project_name ?? 'No project name' }}</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
                            ${{ number_format($quote->total_sell, 0) }} • {{ $quote->created_at->format('M d, Y') }}
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</div>
@endsection
