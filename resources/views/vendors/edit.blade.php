@extends('layouts.app')
@section('title', 'Edit ' . $vendor->name)
@section('page-title', 'Edit Vendor')

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Edit Vendor Information</div>
    </div>
    <form method="POST" action="{{ route('vendors.update', $vendor) }}">
        @csrf @method('PUT')
        <div class="form-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Vendor Name <span class="required-star">*</span></label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $vendor->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="specialty">Specialty</label>
                    <input id="specialty" name="specialty" type="text" class="form-control" value="{{ old('specialty', $vendor->specialty) }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="contact_person">Contact Person</label>
                    <input id="contact_person" name="contact_person" type="text" class="form-control" value="{{ old('contact_person', $vendor->contact_person) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="default_email">Default Email</label>
                    <input id="default_email" name="default_email" type="email" class="form-control" value="{{ old('default_email', $vendor->default_email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone', $vendor->phone) }}">
                </div>
            </div>
            <div class="form-row single">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vendor->is_active) ? 'checked' : '' }} style="width:16px;height:16px">
                    <span style="font-size:13.5px;color:var(--text-primary);font-weight:500">Active Vendor</span>
                </label>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input id="country" name="country" type="text" class="form-control" value="{{ old('country', $vendor->country) }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="notes">Internal Notes</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes', $vendor->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="save-vendor-btn">Save Changes</button>
            <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
