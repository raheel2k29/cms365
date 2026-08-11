<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        // Only show Customer (Company) contacts in the main Contacts tab
        $query = Contact::with('company')->whereNotNull('company_id');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($company = $request->get('company_id')) {
            $query->where('company_id', $company);
        }

        $contacts  = $query->orderBy('name')->paginate(15)->withQueryString();
        $companies = Company::orderBy('name')->pluck('name', 'id');

        return view('contacts.index', compact('contacts', 'companies'));
    }

    public function create(Request $request)
    {
        $companies = Company::orderBy('name')->pluck('name', 'id');
        $vendor = null;
        if ($request->has('vendor_id')) {
            $vendor = \App\Models\Vendor::find($request->vendor_id);
        }
        return view('contacts.create', compact('companies', 'vendor'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'    => 'nullable|exists:companies,id',
            'vendor_id'     => 'nullable|exists:vendors,id',
            'rep_agency_id' => 'nullable|exists:rep_agencies,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'position'      => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'is_primary'    => 'boolean',
        ]);

        $data['is_primary'] = $request->boolean('is_primary');
        $contact = Contact::create($data);

        if ($contact->vendor_id) {
            return redirect()->route('vendors.show', $contact->vendor_id)
                ->with('success', "Vendor Contact '{$contact->name}' created successfully.");
        }
        if ($contact->rep_agency_id) {
            return redirect()->route('rep-agencies.show', $contact->rep_agency_id)
                ->with('success', "Rep Agency Contact '{$contact->name}' created successfully.");
        }

        return redirect()->route('contacts.show', $contact)
            ->with('success', "Contact '{$contact->name}' created successfully.");
    }

    public function show(Contact $contact)
    {
        $contact->load(['company', 'quotes' => fn($q) => $q->latest()->limit(5)]);
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $companies = Company::orderBy('name')->pluck('name', 'id');
        return view('contacts.edit', compact('contact', 'companies'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'company_id'    => 'nullable|exists:companies,id',
            'vendor_id'     => 'nullable|exists:vendors,id',
            'rep_agency_id' => 'nullable|exists:rep_agencies,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'position'      => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'is_primary'    => 'boolean',
        ]);

        $data['is_primary'] = $request->boolean('is_primary');
        $contact->update($data);

        if ($contact->vendor_id) {
            return redirect()->route('vendors.show', $contact->vendor_id)
                ->with('success', 'Vendor Contact updated successfully.');
        }
        if ($contact->rep_agency_id) {
            return redirect()->route('rep-agencies.show', $contact->rep_agency_id)
                ->with('success', 'Rep Agency Contact updated successfully.');
        }

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $vendorId = $contact->vendor_id;
        $repAgencyId = $contact->rep_agency_id;
        $contact->delete();
        
        if ($vendorId) {
            return redirect()->route('vendors.show', $vendorId)->with('success', 'Contact deleted successfully.');
        }
        
        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:contacts,id',
        ]);

        Contact::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' contacts deleted successfully.');
    }
}
