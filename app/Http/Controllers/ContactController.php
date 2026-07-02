<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('company');

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

    public function create()
    {
        $companies = Company::orderBy('name')->pluck('name', 'id');
        return view('contacts.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'position'   => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $data['is_primary'] = $request->boolean('is_primary');
        $contact = Contact::create($data);

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
            'company_id' => 'nullable|exists:companies,id',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'position'   => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $data['is_primary'] = $request->boolean('is_primary');
        $contact->update($data);

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')
            ->with('success', 'Contact archived successfully.');
    }
}
