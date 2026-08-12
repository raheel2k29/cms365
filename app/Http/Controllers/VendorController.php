<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::withCount('quoteRequests');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('default_email', 'like', "%{$search}%")
                  ->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        $vendors   = $query->orderBy('name')->paginate(15)->withQueryString();
        $countries = Vendor::whereNotNull('country')->distinct()->orderBy('country')->pluck('country');

        return view('vendors.index', compact('vendors', 'countries'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'default_email'  => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'country'        => 'nullable|string|max:255',
            'specialty'      => 'nullable|string|max:255',
            'is_active'      => 'boolean',
            'notes'          => 'nullable|string',
            'contacts'       => 'nullable|array',
            'contacts.*.name'=> 'required_with:contacts|string|max:255',
            'contacts.*.email'=> 'required_with:contacts|email|max:255',
            'contacts.*.position'=> 'nullable|string|max:255',
            'contacts.*.phone'=> 'nullable|string|max:50',
        ]);

        $data['is_active'] = $request->has('is_active');
        $vendor = Vendor::create($data);

        if (!empty($data['contacts'])) {
            foreach ($data['contacts'] as $contactData) {
                $vendor->contacts()->create($contactData);
            }
        }

        return redirect()->route('vendors.show', $vendor)
            ->with('success', "Vendor '{$vendor->name}' created successfully.");
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['quoteRequests.quote', 'contacts']);
        $vendor->loadCount('quoteRequests');
        
        $vendorItems = $vendor->items()->paginate(20);
        
        return view('vendors.show', compact('vendor', 'vendorItems'));
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('contacts');
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'default_email'  => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'country'        => 'nullable|string|max:255',
            'specialty'      => 'nullable|string|max:255',
            'is_active'      => 'boolean',
            'notes'          => 'nullable|string',
            'contacts'       => 'nullable|array',
            'contacts.*.id'  => 'nullable|exists:contacts,id',
            'contacts.*.name'=> 'required_with:contacts|string|max:255',
            'contacts.*.email'=> 'required_with:contacts|email|max:255',
            'contacts.*.position'=> 'nullable|string|max:255',
            'contacts.*.phone'=> 'nullable|string|max:50',
        ]);

        $data['is_active'] = $request->has('is_active');
        $vendor->update($data);

        $submittedContactIds = collect($request->input('contacts', []))->pluck('id')->filter()->toArray();
        // Delete contacts that were removed from the form
        $vendor->contacts()->whereNotIn('id', $submittedContactIds)->delete();

        if (!empty($data['contacts'])) {
            foreach ($data['contacts'] as $contactData) {
                if (!empty($contactData['id'])) {
                    $vendor->contacts()->where('id', $contactData['id'])->update([
                        'name' => $contactData['name'],
                        'email' => $contactData['email'],
                        'position' => $contactData['position'],
                        'phone' => $contactData['phone'],
                    ]);
                } else {
                    $vendor->contacts()->create($contactData);
                }
            }
        }

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:vendors,id',
        ]);

        Vendor::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' vendors deleted successfully.');
    }
}
