@extends('layouts.app')
@section('title', 'New Vendor')
@section('page-title', 'New Vendor')

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Vendor Information</div>
    </div>
    <form method="POST" action="{{ route('vendors.store') }}">
        @csrf
        <div class="form-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Vendor Name <span class="required-star">*</span></label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="specialty">Specialty</label>
                    <input id="specialty" name="specialty" type="text" class="form-control" value="{{ old('specialty') }}" placeholder="e.g. Lighting, Cables, etc.">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="contact_person">Contact Person</label>
                    <input id="contact_person" name="contact_person" type="text" class="form-control" value="{{ old('contact_person') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="default_email">Default Email</label>
                    <input id="default_email" name="default_email" type="email" class="form-control" value="{{ old('default_email') }}" placeholder="For automated RFQs">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone') }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input id="country" name="country" type="text" class="form-control" value="{{ old('country') }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="notes">Internal Notes</label>
                    <textarea id="notes" name="notes" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="save-vendor-btn">Save Vendor</button>
            <a href="{{ route('vendors.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
