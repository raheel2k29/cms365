<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Company;
use App\Models\Contact;
use App\Models\QuoteType;
use App\Models\BusinessEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with(['company', 'contact', 'assignedUser', 'quoteType'])
            ->orderByDesc('created_at')
            ->get();
            
        return view('quotes.index', compact('quotes'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $contacts = Contact::orderBy('name')->get();
        $quoteTypes = QuoteType::where('is_active', true)->get();
        
        return view('quotes.create', compact('companies', 'contacts', 'quoteTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'nullable|string|max:255',
            'project_address' => 'nullable|string|max:255',
            'due_at'       => 'nullable|date',
            'expires_at'   => 'nullable|date',
            'company_id'   => 'nullable|exists:companies,id',
            'contact_id'   => 'nullable|exists:contacts,id',
            'quote_type_id'=> 'nullable|exists:quote_types,id',
        ]);

        $businessEntity = BusinessEntity::where('code', 'ESC')->first() ?? BusinessEntity::first();

        $quote = new Quote($validated);
        $quote->quote_number = Quote::generateNumber();
        $quote->business_entity_id = $businessEntity->id;
        $quote->assigned_to = auth()->id();
        $quote->status = 'new';
        $quote->save();

        // Log creation
        $quote->activityLogs()->create([
            'user_id' => auth()->id(),
            'action'  => 'created',
            'description' => 'Quote created.'
        ]);

        return redirect()->route('quotes.edit', $quote)->with('success', 'Quote initialized. Please build your line items.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['company', 'contact', 'assignedUser', 'items', 'attachments', 'notes.user', 'activityLogs.user']);
        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $quote->load('items');
        $companies = Company::orderBy('name')->get();
        $contacts = Contact::orderBy('name')->get();
        $quoteTypes = QuoteType::where('is_active', true)->get();
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        
        return view('quotes.edit', compact('quote', 'companies', 'contacts', 'quoteTypes', 'vendors'));
    }

    public function update(Request $request, Quote $quote)
    {
        // This method will handle updating the quote metadata AND saving items from the dynamic builder
        $validated = $request->validate([
            'project_name' => 'nullable|string|max:255',
            'project_address' => 'nullable|string|max:255',
            'due_at'       => 'nullable|date',
            'expires_at'   => 'nullable|date',
            'company_id'   => 'nullable|exists:companies,id',
            'contact_id'   => 'nullable|exists:contacts,id',
            'quote_type_id'=> 'nullable|exists:quote_types,id',
            'status'       => 'nullable|string',
            'items'        => 'nullable|array',
            'items.*.description' => 'nullable|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.cost_price'  => 'required|numeric|min:0',
            'items.*.sell_price'  => 'required|numeric|min:0',
            'items.*.type'        => 'nullable|string',
            'items.*.rep'         => 'nullable|string',
            'items.*.vendor_id'   => 'nullable|exists:vendors,id',
            'items.*.unit'        => 'nullable|string',
            'items.*.quoted_by'   => 'nullable|string',
            'items.*.line_note'   => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $quote, $validated) {
            $oldStatus = $quote->status;
            
            $quote->fill($request->only(['project_name', 'project_address', 'due_at', 'expires_at', 'company_id', 'contact_id', 'quote_type_id']));
            if ($request->has('status') && $request->status !== $quote->status) {
                $quote->status = $request->status;
                $quote->activityLogs()->create([
                    'user_id' => auth()->id(),
                    'action'  => 'status_changed',
                    'description' => "Status changed from {$oldStatus} to {$quote->status}."
                ]);
            }
            
            // Sync Items
            if ($request->has('items')) {
                $quote->items()->delete(); // simplify by replacing
                foreach ($validated['items'] as $index => $itemData) {
                    $lineTotal = $itemData['qty'] * $itemData['sell_price'];
                    $marginPct = $itemData['sell_price'] > 0 ? (($itemData['sell_price'] - $itemData['cost_price']) / $itemData['sell_price']) * 100 : 0;
                    
                    $quote->items()->create([
                        'sort_order' => $index,
                        'description' => $itemData['description'] ?? 'Item',
                        'qty' => $itemData['qty'],
                        'cost_price' => $itemData['cost_price'],
                        'sell_price' => $itemData['sell_price'],
                        'line_total' => $lineTotal,
                        'margin_pct' => $marginPct,
                        'type' => $itemData['type'] ?? null,
                        'rep' => $itemData['rep'] ?? null,
                        'vendor_id' => $itemData['vendor_id'] ?? null,
                        'unit' => $itemData['unit'] ?? null,
                        'quoted_by' => $itemData['quoted_by'] ?? null,
                        'line_note' => $itemData['line_note'] ?? null,
                    ]);
                }
            }
            
            $quote->calculateTotals();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'quote' => $quote->fresh(['items'])]);
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote updated successfully.');
    }

    public function pdf(Quote $quote)
    {
        $quote->load(['company', 'contact', 'items.vendor']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotes.pdf', compact('quote'));
        return $pdf->download("Quote_{$quote->quote_number}.pdf");
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Quote deleted.');
    }
}
