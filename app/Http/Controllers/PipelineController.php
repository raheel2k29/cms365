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
        $statuses = [
            'new' => 'New',
            'in_review' => 'In Review',
            'rfq_sent' => 'RFQ Sent',
            'pricing_received' => 'Pricing Received',
            'quote_prepared' => 'Quote Prepared',
            'quote_sent' => 'Quote Sent',
            'submitted' => 'Submitted'
        ];

        // Base query for open quotes
        $query = Quote::with(['company'])
            ->whereIn('status', array_keys($statuses));
            
        // Handle filter
        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('status', $request->stage);
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
        $awardedValue = Quote::where('status', 'won')->sum('total_sell'); // all time for now
        
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
