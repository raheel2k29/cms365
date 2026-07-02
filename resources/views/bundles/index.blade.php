@extends('layouts.app')
@section('title', 'Bundles')
@section('page-title', 'Product Bundles')

@section('topbar-actions')
    <a href="#" class="btn btn-primary btn-sm" id="new-bundle-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Bundle
    </a>
@endsection

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')

{{-- List card --}}
<div class="list-card">
    <div class="list-card-header">
        <div class="list-card-title">All Bundles</div>
    </div>

    <div style="display:flex;flex-direction:column;align-items:center;padding:48px;gap:10px;text-align:center">
        <div style="font-size:40px;opacity:0.2">📦</div>
        <div style="font-size:15px;font-weight:600;color:var(--text-secondary)">No bundles yet</div>
        <div style="font-size:13px;color:var(--text-muted)">Add your first product bundle to get started</div>
        <a href="#" class="btn btn-primary" style="margin-top:8px">+ New Bundle</a>
    </div>
</div>
@endsection
