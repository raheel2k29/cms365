<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\QuoteStatus;
use Illuminate\Http\Request;

class QuoteStatusController extends Controller
{
    public function index()
    {
        $statuses = QuoteStatus::where('business_entity_id', auth()->user()->business_entity_id)
            ->orderBy('order_index')
            ->get();
            
        return view('settings.quote-statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = QuoteStatus::where('business_entity_id', auth()->user()->business_entity_id)->max('order_index');

        QuoteStatus::create([
            'business_entity_id' => auth()->user()->business_entity_id,
            'name' => $request->name,
            'color' => $request->color ?? '#cbd5e1',
            'order_index' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Quote status added successfully.');
    }

    public function update(Request $request, QuoteStatus $quoteStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $quoteStatus->update([
            'name' => $request->name,
            'color' => $request->color ?? '#cbd5e1',
        ]);

        return back()->with('success', 'Quote status updated successfully.');
    }

    public function destroy(QuoteStatus $quoteStatus)
    {
        if ($quoteStatus->quotes()->count() > 0) {
            return back()->with('error', 'Cannot delete this status because it is assigned to one or more quotes.');
        }

        $quoteStatus->delete();
        return back()->with('success', 'Quote status deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:quote_statuses,id'
        ]);

        foreach ($request->order as $index => $id) {
            QuoteStatus::where('id', $id)
                ->where('business_entity_id', auth()->user()->business_entity_id)
                ->update(['order_index' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
