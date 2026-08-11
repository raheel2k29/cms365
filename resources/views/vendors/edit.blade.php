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

            <div style="margin-top:32px; border-top:1px solid var(--border-color); padding-top:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div style="font-weight:600; font-size:16px; color:var(--text-primary)">Vendor Contacts</div>
                    <button type="button" class="btn btn-ghost btn-sm" id="add-contact-btn">+ Add Contact</button>
                </div>
                
                <div id="contacts-container">
                    @foreach($vendor->contacts as $index => $contact)
                    <div class="contact-row" style="background:var(--bg-body); padding:16px; border-radius:8px; margin-bottom:12px; border:1px solid var(--border-color); display:flex; gap:16px; align-items:flex-start;">
                        <input type="hidden" name="contacts[{{ $index }}][id]" value="{{ $contact->id }}">
                        <div style="flex:1; display:flex; flex-direction:column; gap:12px;">
                            <div style="display:flex; gap:12px;">
                                <div style="flex:1;">
                                    <label class="form-label" style="font-size:11px;">Name *</label>
                                    <input type="text" name="contacts[{{ $index }}][name]" value="{{ $contact->name }}" class="form-control" required style="padding:6px 10px; font-size:13px;">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label" style="font-size:11px;">Title/Position</label>
                                    <input type="text" name="contacts[{{ $index }}][position]" value="{{ $contact->position }}" class="form-control" style="padding:6px 10px; font-size:13px;">
                                </div>
                            </div>
                            <div style="display:flex; gap:12px;">
                                <div style="flex:1;">
                                    <label class="form-label" style="font-size:11px;">Email *</label>
                                    <input type="email" name="contacts[{{ $index }}][email]" value="{{ $contact->email }}" class="form-control" required style="padding:6px 10px; font-size:13px;">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label" style="font-size:11px;">Phone</label>
                                    <input type="text" name="contacts[{{ $index }}][phone]" value="{{ $contact->phone }}" class="form-control" style="padding:6px 10px; font-size:13px;">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm remove-contact-btn" style="color:var(--danger); margin-top:24px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="save-vendor-btn">Save Changes</button>
            <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('contacts-container');
        const addBtn = document.getElementById('add-contact-btn');
        let contactIndex = {{ $vendor->contacts->count() > 0 ? $vendor->contacts->count() : 0 }};

        function addContactRow() {
            const row = document.createElement('div');
            row.classList.add('contact-row');
            row.style.cssText = 'background:var(--bg-body); padding:16px; border-radius:8px; margin-bottom:12px; border:1px solid var(--border-color); display:flex; gap:16px; align-items:flex-start;';
            row.innerHTML = `
                <div style="flex:1; display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; gap:12px;">
                        <div style="flex:1;">
                            <label class="form-label" style="font-size:11px;">Name *</label>
                            <input type="text" name="contacts[${contactIndex}][name]" class="form-control" required style="padding:6px 10px; font-size:13px;">
                        </div>
                        <div style="flex:1;">
                            <label class="form-label" style="font-size:11px;">Title/Position</label>
                            <input type="text" name="contacts[${contactIndex}][position]" class="form-control" style="padding:6px 10px; font-size:13px;">
                        </div>
                    </div>
                    <div style="display:flex; gap:12px;">
                        <div style="flex:1;">
                            <label class="form-label" style="font-size:11px;">Email *</label>
                            <input type="email" name="contacts[${contactIndex}][email]" class="form-control" required style="padding:6px 10px; font-size:13px;">
                        </div>
                        <div style="flex:1;">
                            <label class="form-label" style="font-size:11px;">Phone</label>
                            <input type="text" name="contacts[${contactIndex}][phone]" class="form-control" style="padding:6px 10px; font-size:13px;">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm remove-contact-btn" style="color:var(--danger); margin-top:24px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px; height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            
            row.querySelector('.remove-contact-btn').addEventListener('click', function() {
                row.remove();
            });

            container.appendChild(row);
            contactIndex++;
        }

        addBtn.addEventListener('click', addContactRow);
        
        // Add remove listener to existing rows
        document.querySelectorAll('.remove-contact-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.contact-row').remove();
            });
        });
        
        if (contactIndex === 0) addContactRow();
    });
</script>
@endpush
</div>
@endsection
