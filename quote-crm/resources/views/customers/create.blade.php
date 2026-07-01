@extends('layouts.app')
@section('title', 'New Company')
@section('page-title', 'New Company')

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Company Information</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Add a new customer company to your directory</div>
    </div>
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="form-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Company Name <span class="required-star">*</span></label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" placeholder="e.g. Control Electric" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="code">Company Code</label>
                    <input id="code" name="code" type="text" class="form-control" value="{{ old('code') }}" placeholder="e.g. CTRL-ELEC">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="industry">Industry</label>
                    <input id="industry" name="industry" type="text" class="form-control" value="{{ old('industry') }}" placeholder="e.g. Electrical Contractors">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input id="country" name="country" type="text" class="form-control" value="{{ old('country') }}" placeholder="e.g. United States">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input id="website" name="website" type="text" class="form-control" value="{{ old('website') }}" placeholder="https://example.com">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <input id="address" name="address" type="text" class="form-control" value="{{ old('address') }}" placeholder="Street, City, State, ZIP">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Internal notes about this company...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="save-company-btn">Save Company</button>
            <a href="{{ route('customers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
