@extends('layouts.app')
@section('title', 'Business Entities')
@section('page-title', 'Business Entities')

@section('topbar-actions')
    <a href="{{ route('settings.business-entities.create') }}" class="btn btn-primary btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Entity
    </a>
@endsection

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="list-card">
    <div class="list-card-header">
        <div class="list-card-title">Your Operating Entities</div>
    </div>
    @if($entities->isEmpty())
        <div style="padding:48px;text-align:center">
            <div style="font-size:15px;font-weight:600;color:var(--text-secondary)">No entities configured.</div>
            <a href="{{ route('settings.business-entities.create') }}" class="btn btn-primary" style="margin-top:8px">+ Add Entity</a>
        </div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Quotes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($entities as $entity)
                <tr>
                    <td><span style="font-weight:600;color:var(--text-primary)">{{ $entity->code }}</span></td>
                    <td>{{ $entity->name }}</td>
                    <td>{{ $entity->email ?? '—' }}</td>
                    <td>{{ $entity->phone ?? '—' }}</td>
                    <td>
                        @if($entity->is_active)
                            <span style="background:var(--success-soft);color:var(--success);font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px">ACTIVE</span>
                        @else
                            <span style="background:var(--danger-soft);color:var(--danger);font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px">INACTIVE</span>
                        @endif
                    </td>
                    <td>
                        <span style="background:var(--accent-soft);color:var(--accent);font-size:12px;font-weight:600;padding:2px 8px;border-radius:20px">
                            {{ $entity->quotes_count }}
                        </span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="{{ route('settings.business-entities.edit', $entity) }}" class="btn-icon" title="Edit"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
