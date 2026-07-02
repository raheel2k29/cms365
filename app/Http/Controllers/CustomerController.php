<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount(['contacts', 'quotes']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($country = $request->get('country')) {
            $query->where('country', $country);
        }

        $companies = $query->orderBy('name')->paginate(15)->withQueryString();
        $countries  = Company::whereNotNull('country')->distinct()->orderBy('country')->pluck('country');

        return view('customers.index', compact('companies', 'countries'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:255',
            'industry'  => 'nullable|string|max:255',
            'country'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'website'   => 'nullable|string|max:255',
            'address'   => 'nullable|string|max:500',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $company = Company::create($data);

        return redirect()->route('customers.show', $company)
            ->with('success', "Company '{$company->name}' created successfully.");
    }

    public function show(Company $customer)
    {
        $customer->loadCount(['contacts', 'quotes']);
        $customer->load(['contacts', 'quotes' => fn($q) => $q->latest()->limit(5)]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Company $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Company $customer)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:255',
            'industry'  => 'nullable|string|max:255',
            'country'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'website'   => 'nullable|string|max:255',
            'address'   => 'nullable|string|max:500',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $customer->update($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', 'Company archived successfully.');
    }
}
