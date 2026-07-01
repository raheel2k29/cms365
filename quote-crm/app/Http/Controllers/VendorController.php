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
        ]);

        $data['is_active'] = $request->has('is_active');
        $vendor = Vendor::create($data);

        return redirect()->route('vendors.show', $vendor)
            ->with('success', "Vendor '{$vendor->name}' created successfully.");
    }

    public function show(Vendor $vendor)
    {
        $vendor->loadCount('quoteRequests');
        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
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
        ]);

        $data['is_active'] = $request->has('is_active');
        $vendor->update($data);

        return redirect()->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')
            ->with('success', 'Vendor archived successfully.');
    }
}
