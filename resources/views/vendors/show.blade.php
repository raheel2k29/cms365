@extends('layouts.app')
@section('title', $vendor->name)
@section('page-title', 'Vendor Profile')

@section('topbar-actions')
    <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-ghost btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Edit
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
                <div class="detail-avatar" style="background:var(--warning-soft);color:var(--warning)">{{ strtoupper(substr($vendor->name, 0, 2)) }}</div>
                <div>
                    <div class="detail-name">
                        {{ $vendor->name }}
                        @if(!$vendor->is_active)
                            <span style="background:var(--danger-soft);color:var(--danger);font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:6px;vertical-align:middle">INACTIVE</span>
                        @endif
                    </div>
                    <div class="detail-sub">{{ $vendor->specialty ?? 'No specialty specified' }}</div>
                </div>
            </div>
            <div class="detail-body">
                <div class="form-row single">
                    <div class="detail-field">
                        <div class="detail-field-label">Contact Person</div>
                        <div class="detail-field-value">{{ $vendor->contact_person ?? '—' }}</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="detail-field">
                        <div class="detail-field-label">Default Email</div>
                        <div class="detail-field-value">
                            @if($vendor->default_email)
                                <a href="mailto:{{ $vendor->default_email }}" style="color:var(--text-primary);text-decoration:none">{{ $vendor->default_email }}</a>
                            @else — @endif
                        </div>
                    </div>
                    <div class="detail-field">
                        <div class="detail-field-label">Phone</div>
                        <div class="detail-field-value">
                            @if($vendor->phone)
                                <a href="tel:{{ $vendor->phone }}" style="color:var(--text-primary);text-decoration:none">{{ $vendor->phone }}</a>
                            @else — @endif
                        </div>
                    </div>
                </div>
                <div class="form-row single">
                    <div class="detail-field">
                        <div class="detail-field-label">Country</div>
                        <div class="detail-field-value">{{ $vendor->country ?? '—' }}</div>
                    </div>
                </div>
                @if($vendor->notes)
                <div class="detail-field" style="margin-top:8px">
                    <div class="detail-field-label">Notes</div>
                    <div class="detail-field-value" style="white-space:pre-wrap;color:var(--text-secondary)">{{ $vendor->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div style="display:flex;flex-direction:column;gap:20px">
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Contacts <span class="list-count">({{ $vendor->contacts->count() }})</span></div>
            </div>
            @if($vendor->contacts->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
                    No contacts added yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->contacts as $contact)
                            <tr>
                                <td>
                                    <div style="font-weight:500;color:var(--text-primary)">{{ $contact->name }}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $contact->position ?? 'No title' }}</div>
                                </td>
                                <td><a href="mailto:{{ $contact->email }}" style="color:var(--text-primary);text-decoration:none">{{ $contact->email }}</a></td>
                                <td>{{ $contact->phone ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="list-card">
            <div class="list-card-header">
                <div class="list-card-title">Quote Requests <span class="list-count">({{ $vendor->quote_requests_count }})</span></div>
            </div>
            @if($vendor->quoteRequests->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
                    No quote requests have been sent to this vendor yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Quote Project</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendor->quoteRequests as $request)
                            <tr>
                                <td>{{ $request->requested_at ? \Carbon\Carbon::parse($request->requested_at)->format('M d, Y') : 'Unknown' }}</td>
                                <td>{{ $request->quote ? $request->quote->project_name : 'Deleted Quote' }}</td>
                                <td>
                                    @if($request->status === 'pending')
                                        <span style="background:var(--warning-soft);color:var(--warning);padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600">Pending</span>
                                    @elseif($request->status === 'received')
                                        <span style="background:var(--success-soft);color:var(--success);padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600">Received</span>
                                    @else
                                        <span style="background:var(--bg-card-hover);padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600">{{ ucfirst($request->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->quote)
                                        <a href="{{ route('quotes.show', $request->quote) }}" class="btn btn-ghost btn-sm">View Quote</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="list-card" style="margin-top: 24px;">
    <div class="list-card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="list-card-title">Catalog Items <span class="list-count">({{ $vendorItems->total() }})</span></div>
        <form method="GET" action="{{ route('vendors.show', $vendor) }}" style="display:flex; gap:10px;">
            <input type="text" name="search" class="form-control" placeholder="Search items..." value="{{ request('search') }}" style="width: 250px; padding:6px 12px; font-size:13px;">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-ghost btn-sm">Clear</a>
            @endif
        </form>
    </div>
    
    @if($vendorItems->isEmpty())
        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No items in catalog.</div>
    @else
        <table class="table" style="margin-bottom:0;">
            <thead>
                <tr>
                    <th style="padding:12px 20px;">SKU</th>
                    <th style="padding:12px 20px;">Description</th>
                    <th style="padding:12px 20px;">Cost</th>
                    <th style="padding:12px 20px;">Sell</th>
                    <th style="padding:12px 20px;">Unit</th>
                    <th style="padding:12px 20px; width:50px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendorItems as $item)
                <tr>
                    <td style="padding:12px 20px;">{{ $item->item_number ?: '—' }}</td>
                    <td style="padding:12px 20px;">{{ $item->description }}</td>
                    <td style="padding:12px 20px;">${{ number_format($item->cost_price, 2) }}</td>
                    <td style="padding:12px 20px;">${{ number_format($item->sell_price, 2) }}</td>
                    <td style="padding:12px 20px;">{{ $item->unit ?: '—' }}</td>
                    <td style="padding:12px 20px; text-align:right;">
                        <form action="{{ route('catalog.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this catalog item?');" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--danger);padding:4px;" title="Delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 12px 20px;">
            {{ $vendorItems->links() }}
        </div>
    @endif
</div>

<div class="list-card" style="margin-top: 24px;">
    <div class="list-card-header">
        <div class="list-card-title">Import Pricing Catalog</div>
    </div>
    <div style="padding: 20px;">
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
            Upload a CSV or Excel file to quickly import or update catalog items for this vendor. Format: Item Number, Description, Cost, Sell, Unit (no headers required).
        </p>
        <form action="{{ route('catalog.import') }}" method="POST" enctype="multipart/form-data" style="display:flex; gap:16px; align-items:center;">
            @csrf
            <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
            <input type="file" name="csv_file" accept=".csv, .xlsx" class="form-control" required style="padding: 6px; flex:1;">
            <button type="submit" class="btn btn-primary">Import CSV/Excel</button>
        </form>
    </div>
</div>
@endsection
