@extends('layouts.app')
@section('title','New Quote')
@section('page-title','Create New Quote')
@section('topbar-actions')
<a href="{{ route('quotes.index') }}" class="btn btn-ghost">Cancel</a>
@endsection
@section('content')
<div style="max-width:600px;margin:0 auto;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);padding:24px">
    <form action="{{ route('quotes.store') }}" method="POST">
        @csrf
        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Project Name <span style="color:var(--text-muted);font-weight:400">(Optional)</span></label>
            <input type="text" name="project_name" class="form-control" style="width:100%;padding:10px 14px;font-size:14px" placeholder="e.g. Acme HQ Renovation">
            @error('project_name')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Project Address <span style="color:var(--text-muted);font-weight:400">(Optional)</span></label>
            <input type="text" name="project_address" class="form-control" style="width:100%;padding:10px 14px;font-size:14px" placeholder="e.g. 123 Main St">
            @error('project_address')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Due Date and Time <span style="color:var(--text-muted);font-weight:400">(Optional)</span></label>
            <input type="datetime-local" name="due_at" class="form-control" style="width:100%;padding:10px 14px;font-size:14px">
            @error('due_at')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Expiration Date</label>
            <input type="date" name="expires_at" class="form-control" style="width:100%;padding:10px 14px;font-size:14px" value="{{ \Carbon\Carbon::now()->addDays(30)->format('Y-m-d') }}">
            @error('expires_at')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Customer / Company</label>
            <select name="company_id" class="form-control" style="width:100%;padding:10px 14px;font-size:14px">
                <option value="">-- Select Customer --</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
            @error('company_id')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Primary Contact</label>
            <select name="contact_id" class="form-control" style="width:100%;padding:10px 14px;font-size:14px">
                <option value="">-- Select Contact --</option>
                @foreach($contacts as $contact)
                    <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->company->name ?? 'No Company' }})</option>
                @endforeach
            </select>
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px">Tip: You can select a contact directly, or leave it blank if unknown.</div>
            @error('contact_id')<div style="color:#dc2626;font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:24px">
            <label class="form-label" style="display:block;margin-bottom:8px;font-weight:600;font-size:13px">Quote Type</label>
            <select name="quote_type_id" class="form-control" style="width:100%;padding:10px 14px;font-size:14px">
                <option value="">-- Select Type --</option>
                @foreach($quoteTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="border-top:1px solid var(--border);padding-top:20px;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Initialize Quote &rarr;</button>
        </div>
    </form>
</div>
@endsection
