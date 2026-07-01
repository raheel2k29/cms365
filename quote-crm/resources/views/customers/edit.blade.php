@extends('layouts.app')
@section('title', 'Edit ' . $customer->name)
@section('page-title', 'Edit Company')

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Edit Company Information</div>
    </div>
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf @method('PUT')
        <div class="form-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Company Name <span class="required-star">*</span></label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $customer->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="code">Company Code</label>
                    <input id="code" name="code" type="text" class="form-control" value="{{ old('code', $customer->code) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="industry">Industry</label>
                    <input id="industry" name="industry" type="text" class="form-control" value="{{ old('industry', $customer->industry) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone', $customer->phone) }}">
                </div>
            </div>
            <div class="form-row single">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }} style="width:16px;height:16px">
                    <span style="font-size:13.5px;color:var(--text-primary);font-weight:500">Active Company</span>
                </label>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input id="country" name="country" type="text" class="form-control" value="{{ old('country', $customer->country) }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input id="website" name="website" type="text" class="form-control" value="{{ old('website', $customer->website) }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <input id="address" name="address" type="text" class="form-control" value="{{ old('address', $customer->address) }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="save-company-btn">Save Changes</button>
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
