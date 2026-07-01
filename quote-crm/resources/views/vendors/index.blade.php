@extends('layouts.app')
@section('title', 'Vendors')
@section('page-title', 'Vendors')

@section('topbar-actions')
    <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm" id="new-vendor-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Vendor
    </a>
@endsection

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<form method="GET" action="{{ route('vendors.index') }}">
<div class="page-toolbar">
    <div class="search-box">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" placeholder="Search vendors..." value="{{ request('search') }}">
    </div>
    <select name="country" class="filter-select" onchange="this.form.submit()">
        <option value="">All Countries</option>
        @foreach($countries as $c)
            <option value="{{ $c }}" {{ request('country') == $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
    </select>
    @if(request('search') || request('country'))
        <a href="{{ route('vendors.index') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
    <span style="margin-left:auto;font-size:12.5px;color:var(--text-muted)">{{ $vendors->total() }} vendors</span>
</div>
</form>

<div class="list-card">
    <div class="list-card-header">
        <div class="list-card-title">All Vendors</div>
    </div>
    @if($vendors->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;padding:48px;gap:10px;text-align:center">
            <div style="font-size:40px;opacity:0.2">🏭</div>
            <div style="font-size:15px;font-weight:600;color:var(--text-secondary)">No vendors yet</div>
            <div style="font-size:13px;color:var(--text-muted)">Add your first vendor to start requesting pricing</div>
            <a href="{{ route('vendors.create') }}" class="btn btn-primary" style="margin-top:8px">+ New Vendor</a>
        </div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>Specialty</th>
                    <th>Contact Person</th>
                    <th>Country</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Quote Requests</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendors as $vendor)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="avatar-initials" style="background:var(--warning-soft);color:var(--warning)">{{ strtoupper(substr($vendor->name,0,2)) }}</div>
                            <div>
                                <a href="{{ route('vendors.show', $vendor) }}" style="color:var(--text-primary);font-weight:600;text-decoration:none;font-size:13.5px">{{ $vendor->name }}</a>
                            </div>
                        </div>
                    </td>
                    <td>{{ $vendor->specialty ?? '—' }}</td>
                    <td>{{ $vendor->contact_person ?? '—' }}</td>
                    <td>{{ $vendor->country ?? '—' }}</td>
                    <td>{{ $vendor->default_email ?? '—' }}</td>
                    <td>{{ $vendor->phone ?? '—' }}</td>
                    <td>
                        <span style="background:var(--accent-soft);color:var(--accent);font-size:12px;font-weight:600;padding:2px 8px;border-radius:20px">
                            {{ $vendor->quote_requests_count }}
                        </span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="{{ route('vendors.show', $vendor) }}" class="btn-icon" title="View"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('vendors.edit', $vendor) }}" class="btn-icon" title="Edit"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('vendors.destroy', $vendor) }}" onsubmit="return confirm('Archive {{ addslashes($vendor->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon danger" title="Archive">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <div class="pagination-info">Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }}</div>
        {{ $vendors->links('vendor.pagination.simple-tailwind') }}
    </div>
    @endif
</div>
@endsection
