@extends('layouts.app')
@section('title', $contact->name)
@section('page-title', 'Contact Profile')

@section('topbar-actions')
    <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-ghost btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
    </a>
    <a href="{{ route('quotes.create', ['contact_id' => $contact->id, 'company_id' => $contact->company_id]) }}" class="btn btn-primary btn-sm">
        + New Quote
    </a>
@endsection

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="detail-grid">
    {{-- Left column --}}
    <div>
        <div class="detail-card">
            <div class="detail-header">
                <div class="detail-avatar" style="background:#f5f3ff;color:#7c3aed">{{ strtoupper(substr($contact->name, 0, 2)) }}</div>
                <div>
                    <div class="detail-name">{{ $contact->name }}
                        @if($contact->is_primary)
                            <span style="background:var(--accent-soft);color:var(--accent);font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:6px;vertical-align:middle">PRIMARY</span>
                        @endif
                    </div>
                    <div class="detail-sub">
                        {{ $contact->position ?? 'No title' }}
                        @if($contact->department)
                            • {{ $contact->department }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="detail-body">
                <div class="form-row single">
                    <div class="detail-field">
                        <div class="detail-field-label">Company</div>
                        <div class="detail-field-value">
                            @if($contact->company)
                                <a href="{{ route('customers.show', $contact->company) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $contact->company->name }}</a>
                            @else — @endif
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="detail-field">
                        <div class="detail-field-label">Email</div>
                        <div class="detail-field-value">
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" style="color:var(--text-primary);text-decoration:none">{{ $contact->email }}</a>
                            @else — @endif
                        </div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-field-label">Phone</div>
                        <div class="detail-field-value">
                            @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}" style="color:var(--text-primary);text-decoration:none">{{ $contact->phone }}</a>
                            @else — @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:20px">
        {{-- Recent Quotes for this contact --}}
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Quotes Requested by {{ explode(' ', $contact->name)[0] }}</div>
            </div>
            @if($contact->quotes->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No quotes linked to this contact.</div>
            @else
                <ul style="list-style:none">
                    @foreach($contact->quotes as $quote)
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
