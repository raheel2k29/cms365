<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $quoteStatuses = \App\Models\QuoteStatus::where('business_entity_id', auth()->user()->business_entity_id)->orderBy('order_index')->get();
        
        $statuses = [];
        $wonStatusId = null;
        $closedStatuses = ['Won', 'Lost', 'Cancelled', 'No BID', 'Missed'];
        
        foreach ($quoteStatuses as $qs) {
            $statuses[$qs->id] = $qs->name;
            if (strtolower($qs->name) === 'won') {
                $wonStatusId = $qs->id;
            }
        }
        
        $openStatusIds = $quoteStatuses->filter(function($s) use ($closedStatuses) {
            return !in_array($s->name, $closedStatuses);
        })->pluck('id')->toArray();

        // Base query for open quotes
        $query = Quote::with(['company', 'quoteStatus'])
            ->where('business_entity_id', auth()->user()->business_entity_id)
            ->whereIn('quote_status_id', $openStatusIds);
            
        // Handle filter
        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('quote_status_id', $request->stage);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhereHas('company', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $openQuotes = $query->get();

        // Top Metrics
        $openPipelineValue = $openQuotes->sum('total_sell');
        
        $weightedPipelineValue = $openQuotes->sum(function($q) {
            return ($q->total_sell * ($q->probability ?? 0)) / 100;
        });
        
        // Awarded (Won)
        $awardedValue = 0;
        if ($wonStatusId) {
            $awardedValue = Quote::where('business_entity_id', auth()->user()->business_entity_id)
                                 ->where('quote_status_id', $wonStatusId)
                                 ->sum('total_sell'); // all time for now
        }
        
        $customersCount = $openQuotes->pluck('company_id')->filter()->unique()->count();

        // Group quotes by Company
        $groupedQuotes = $openQuotes->groupBy(function($q) {
            return $q->company ? $q->company->name : 'Untitled Company';
        });

        return view('pipeline.index', compact(
            'groupedQuotes', 'statuses', 
            'openPipelineValue', 'weightedPipelineValue', 
            'awardedValue', 'customersCount'
        ));
    }

    public function quickUpdate(Request $request)
    {
        $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'field' => 'required|string|in:status,probability,expected_close,next_step',
            'value' => 'nullable'
        ]);

        $quote = Quote::findOrFail($request->quote_id);
        $field = $request->field;
        
        if ($field === 'expected_close') {
            $quote->expected_close = $request->filled('value') ? \Carbon\Carbon::parse($request->value)->format('Y-m-d') : null;
        } else {
            $quote->$field = $request->value;
        }
        
        $quote->save();

        return response()->json(['success' => true]);
    }
}
