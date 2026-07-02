<?php

namespace App\Http\Controllers;

use App\Models\BusinessEntity;
use Illuminate\Http\Request;

class BusinessEntityController extends Controller
{
    public function index(Request $request)
    {
        $entities = BusinessEntity::withCount('quotes')->orderBy('name')->get();
        return view('business-entities.index', compact('entities'));
    }

    public function create()
    {
        return view('business-entities.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:business_entities,code',
            'email'     => 'nullable|email|max:255',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        BusinessEntity::create($data);

        return redirect()->route('settings.business-entities.index')
            ->with('success', 'Business Entity created successfully.');
    }

    public function edit(BusinessEntity $businessEntity)
    {
        return view('business-entities.edit', compact('businessEntity'));
    }

    public function update(Request $request, BusinessEntity $businessEntity)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:50|unique:business_entities,code,'.$businessEntity->id,
            'email'     => 'nullable|email|max:255',
            'phone'     => 'nullable|string|max:50',
            'address'   => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $businessEntity->update($data);

        return redirect()->route('settings.business-entities.index')
            ->with('success', 'Business Entity updated successfully.');
    }
}
