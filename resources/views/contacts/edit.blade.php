@extends('layouts.app')
@section('title', 'Edit ' . $contact->name)
@section('page-title', 'Edit Contact')

@push('styles')
@include('partials.module-styles')
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">Edit Contact Information</div>
    </div>
    <form method="POST" action="{{ route('contacts.update', $contact) }}">
        @csrf @method('PUT')
        <div class="form-card-body">
            <div class="form-row single">
                <div class="form-group">
                    <label class="form-label" for="company_id">Company</label>
                    <select id="company_id" name="company_id" class="form-control">
                        <option value="">-- No Company --</option>
                        @foreach($companies as $id => $name)
                            <option value="{{ $id }}" {{ old('company_id', $contact->company_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name <span class="required-star">*</span></label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $contact->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="position">Position/Title</label>
                    <input id="position" name="position" type="text" class="form-control" value="{{ old('position', $contact->position) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Department</label>
                    <input id="department" name="department" type="text" class="form-control" value="{{ old('department', $contact->department) }}" placeholder="e.g. Sales, Purchasing">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Outlook Email <span class="required-star">*</span></label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $contact->email) }}" required>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" class="form-control" value="{{ old('phone', $contact->phone) }}">
                </div>
            </div>
            <div class="form-row single" style="margin-top:10px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', $contact->is_primary) ? 'checked' : '' }} style="width:16px;height:16px">
                    <span style="font-size:13.5px;color:var(--text-primary);font-weight:500">Primary contact for this company</span>
                </label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="save-contact-btn">Save Changes</button>
            <a href="{{ route('contacts.show', $contact) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
