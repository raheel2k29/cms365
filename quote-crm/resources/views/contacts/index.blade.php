@extends('layouts.app')
@section('title', 'Contacts')
@section('page-title', 'Contacts')

@section('topbar-actions')
    <a href="{{ route('contacts.create') }}" class="btn btn-primary btn-sm" id="new-contact-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Contact
    </a>
@endsection

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<form method="GET" action="{{ route('contacts.index') }}">
<div class="page-toolbar">
    <div class="search-box">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="search" placeholder="Search contacts..." value="{{ request('search') }}">
    </div>
    <select name="company_id" class="filter-select" onchange="this.form.submit()">
        <option value="">All Companies</option>
        @foreach($companies as $id => $name)
            <option value="{{ $id }}" {{ request('company_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
    </select>
    @if(request('search') || request('company_id'))
        <a href="{{ route('contacts.index') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
    <span style="margin-left:auto;font-size:12.5px;color:var(--text-muted)">{{ $contacts->total() }} contacts</span>
</div>
</form>

<div class="list-card">
    <div class="list-card-header">
        <div class="list-card-title">All Contacts</div>
    </div>
    @if($contacts->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;padding:48px;gap:10px;text-align:center">
            <div style="font-size:40px;opacity:0.2">👤</div>
            <div style="font-size:15px;font-weight:600;color:var(--text-secondary)">No contacts yet</div>
            <a href="{{ route('contacts.create') }}" class="btn btn-primary" style="margin-top:8px">+ New Contact</a>
        </div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Position</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="avatar-initials">{{ strtoupper(substr($contact->name,0,2)) }}</div>
                            <div>
                                <a href="{{ route('contacts.show', $contact) }}" style="color:var(--text-primary);font-weight:600;text-decoration:none;font-size:13.5px">{{ $contact->name }}</a>
                                @if($contact->is_primary)
                                    <span style="background:var(--accent-soft);color:var(--accent);font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:4px;vertical-align:middle">PRIMARY</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($contact->company)
                            <a href="{{ route('customers.show', $contact->company) }}" style="color:var(--accent);text-decoration:none">{{ $contact->company->name }}</a>
                        @else — @endif
                    </td>
                    <td>{{ $contact->position ?? '—' }}</td>
                    <td>{{ $contact->email ?? '—' }}</td>
                    <td>{{ $contact->phone ?? '—' }}</td>
                    <td>
                        <div class="row-actions">
                            <a href="{{ route('contacts.show', $contact) }}" class="btn-icon" title="View"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('contacts.edit', $contact) }}" class="btn-icon" title="Edit"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <div class="pagination-info">Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ $contacts->total() }}</div>
        {{ $contacts->links('vendor.pagination.simple-tailwind') }}
    </div>
    @endif
</div>
@endsection
