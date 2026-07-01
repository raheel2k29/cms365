@extends('layouts.app')
@section('title', 'Edit ' . $businessEntity->code)
@section('page-title', 'Edit Business Entity')

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Entity Information</div>
    </div>
    <form method="POST" action="{{ route('settings.business-entities.update', $businessEntity) }}">
        @csrf @method('PUT')
        <div class="form-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="code">Entity Code <span class="required-star">*</span></label>
                    <input id="code" name="code" type="text" class="form-control" value="{{ old('code', $businessEntity->code) }}" required>
                    @error('code')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="name">Entity Name <span class="required-star">*</span></label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $businessEntity->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Billing/Primary Email</label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $businessEntity->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone', $businessEntity->phone) }}">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <input id="address" name="address" type="text" class="form-control" value="{{ old('address', $businessEntity->address) }}">
                </div>
            </div>
            <div class="form-row single">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $businessEntity->is_active) ? 'checked' : '' }} style="width:16px;height:16px">
                    <span style="font-size:13.5px;color:var(--text-primary);font-weight:500">Active Entity</span>
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('settings.business-entities.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
