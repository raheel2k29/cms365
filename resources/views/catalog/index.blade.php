@extends('layouts.app')
@section('title', 'Item Catalog')
@section('page-title', 'Vendor Pricing Catalog')

@push('styles')
<style>
    .catalog-layout {
        display: flex;
        gap: 24px;
    }
    .catalog-main {
        flex: 1;
    }
    .catalog-sidebar {
        width: 320px;
    }
    .import-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
    }
    .import-card h3 {
        margin-bottom: 12px;
        font-size: 15px;
    }
    .template-box {
        background: var(--bg-app);
        padding: 12px;
        border-radius: 6px;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 16px;
        font-family: monospace;
    }
</style>
@endpush

@section('content')
<div class="catalog-layout">
    <div class="catalog-main">
        <div class="list-card">
            <div class="list-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="list-card-title">Catalog Items <span class="list-count">({{ $items->total() }})</span></div>
                <form method="GET" action="{{ route('catalog.index') }}" style="display:flex; gap:10px;">
                    <select name="vendor_id" class="form-control" onchange="this.form.submit()" style="width: 200px;">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" class="form-control" placeholder="Search items..." value="{{ request('search') }}" style="width: 200px;">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
            </div>
            
            <table class="data-table" style="width:100%; text-align:left;">
                <thead>
                    <tr>
                        <th style="padding:12px 20px; border-bottom:1px solid var(--border);">Item / SKU</th>
                        <th style="padding:12px 20px; border-bottom:1px solid var(--border);">Description</th>
                        <th style="padding:12px 20px; border-bottom:1px solid var(--border);">Vendor</th>
                        <th style="padding:12px 20px; border-bottom:1px solid var(--border);">Cost</th>
                        <th style="padding:12px 20px; border-bottom:1px solid var(--border);">Sell</th>
                        <th style="padding:12px 20px; border-bottom:1px solid var(--border);">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td style="padding:12px 20px; border-bottom:1px solid var(--border-light);">{{ $item->item_number ?: '—' }}</td>
                            <td style="padding:12px 20px; border-bottom:1px solid var(--border-light);">{{ $item->description }}</td>
                            <td style="padding:12px 20px; border-bottom:1px solid var(--border-light);">{{ $item->vendor->name ?? '—' }}</td>
                            <td style="padding:12px 20px; border-bottom:1px solid var(--border-light);">${{ number_format($item->cost_price, 2) }}</td>
                            <td style="padding:12px 20px; border-bottom:1px solid var(--border-light);">${{ number_format($item->sell_price, 2) }}</td>
                            <td style="padding:12px 20px; border-bottom:1px solid var(--border-light);">{{ $item->unit ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:24px; text-align:center; color:var(--text-muted);">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div style="padding: 16px 20px;">
                {{ $items->links() }}
            </div>
        </div>
    </div>
    
    <div class="catalog-sidebar">
        <div class="import-card">
            <h3>Import Vendor Pricing</h3>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;">
                Upload a CSV or Excel file to quickly import or update your catalog.
            </p>
            <div class="template-box">
                <strong>Format (No headers required):</strong><br>
                Column A: Item Number / SKU<br>
                Column B: Description<br>
                Column C: Cost Price<br>
                Column D: Sell Price<br>
                Column E: Unit (EA, FT, etc.)
            </div>
            
            <form action="{{ route('catalog.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Select Vendor</label>
                    <select name="vendor_id" class="form-control" required>
                        <option value="">-- Choose Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label>CSV/Excel File</label>
                    <input type="file" name="csv_file" accept=".csv, .xlsx" class="form-control" required style="padding: 6px;">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload & Import</button>
            </form>
        </div>
    </div>
</div>
@endsection
