@extends('layouts.app')
@section('title', 'Request Vendor Pricing')
@section('page-title', 'Request Vendor Pricing - Quote ' . $quote->quote_number)
@section('topbar-actions')
<a href="{{ route('quotes.show', $quote) }}" class="btn btn-ghost">Cancel</a>
@endsection

@section('content')
<div style="max-width:800px; margin:0 auto; background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); padding:24px">
    <form action="{{ route('quotes.vendor-requests.store', $quote) }}" method="POST">
        @csrf
        
        <div style="margin-bottom:24px">
            <label style="display:block; font-weight:600; margin-bottom:8px">1. Select Vendor</label>
            <select name="vendor_id" id="vendor-select" class="form-control" required style="width:100%; padding:10px">
                <option value="">-- Choose Vendor --</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:24px">
            <label style="display:block; font-weight:600; margin-bottom:8px">2. Email To (Contact)</label>
            <select name="contact_email" id="contact-select" class="form-control" required style="width:100%; padding:10px">
                <option value="">-- Select a Vendor First --</option>
            </select>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px">Shows contacts for the selected vendor and their rep agency (if applicable).</div>
        </div>

        <div style="margin-bottom:24px">
            <label style="display:block; font-weight:600; margin-bottom:8px">3. Select Items to Price</label>
            <div style="border:1px solid var(--border); border-radius:6px; overflow:hidden">
                <table style="width:100%; font-size:13px; text-align:left; border-collapse:collapse">
                    <thead style="background:#f8fafc; border-bottom:1px solid var(--border)">
                        <tr>
                            <th style="padding:10px"><input type="checkbox" id="select-all" checked></th>
                            <th style="padding:10px">Description</th>
                            <th style="padding:10px">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quote->items as $item)
                        <tr style="border-bottom:1px solid var(--border-light)">
                            <td style="padding:10px">
                                <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" class="item-checkbox" checked>
                            </td>
                            <td style="padding:10px">{{ $item->description }}</td>
                            <td style="padding:10px">{{ $item->qty }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-bottom:24px">
            <label style="display:block; font-weight:600; margin-bottom:8px">4. Email Message</label>
            <textarea name="message" class="form-control" rows="5" required style="width:100%; padding:10px">Hello,

Could you please provide your best cost pricing and lead times for the following items?

Thank you!</textarea>
            <div style="font-size:11px; color:var(--text-muted); margin-top:4px">A table containing the selected items will be automatically appended to the bottom of this message.</div>
        </div>

        <div style="text-align:right">
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Send Request &rarr;</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('select-all').addEventListener('change', function(e) {
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = e.target.checked);
    });

    const vendorsData = @json($vendors);
    const vendorSelect = document.getElementById('vendor-select');
    const contactSelect = document.getElementById('contact-select');

    vendorSelect.addEventListener('change', function() {
        contactSelect.innerHTML = '<option value="">-- Select Contact --</option>';
        const vendorId = this.value;
        if (!vendorId) return;

        const vendor = vendorsData.find(v => v.id == vendorId);
        if (!vendor) return;

        // Vendor direct contacts
        if (vendor.contacts && vendor.contacts.length > 0) {
            const group = document.createElement('optgroup');
            group.label = vendor.name + " Contacts";
            vendor.contacts.forEach(c => {
                if (c.email) group.innerHTML += `<option value="${c.email}">${c.name} (${c.email})</option>`;
            });
            contactSelect.appendChild(group);
        } else if (vendor.default_email) {
            // Fallback to vendor default email
            contactSelect.innerHTML += `<option value="${vendor.default_email}">${vendor.name} Default (${vendor.default_email})</option>`;
        }

        // Rep Agency contacts
        if (vendor.rep_agency && vendor.rep_agency.contacts && vendor.rep_agency.contacts.length > 0) {
            const group = document.createElement('optgroup');
            group.label = vendor.rep_agency.name + " (Rep Agency)";
            vendor.rep_agency.contacts.forEach(c => {
                if (c.email) group.innerHTML += `<option value="${c.email}">${c.name} (${c.email})</option>`;
            });
            contactSelect.appendChild(group);
        } else if (vendor.rep_agency && vendor.rep_agency.email) {
            contactSelect.innerHTML += `<option value="${vendor.rep_agency.email}">${vendor.rep_agency.name} Default (${vendor.rep_agency.email})</option>`;
        }
    });
</script>
@endsection
