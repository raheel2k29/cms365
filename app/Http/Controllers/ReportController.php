<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfMonth();

        $query = Quote::whereBetween('created_at', [$startDate, $endDate]);

        $totalQuotes = (clone $query)->count();
        $wonQuotes = (clone $query)->where('status', 'won')->count();
        $lostQuotes = (clone $query)->where('status', 'lost')->count();
        
        $resolvedQuotes = $wonQuotes + $lostQuotes;
        $conversionRate = $resolvedQuotes > 0 ? ($wonQuotes / $resolvedQuotes) * 100 : 0;

        $totalValueWon = (clone $query)->where('status', 'won')->sum('total_sell');
        $totalValueLost = (clone $query)->where('status', 'lost')->sum('total_sell');
        
        $openStatuses = ['new', 'pricing_received', 'in_review', 'quote_sent'];
        $pipelineValue = (clone $query)->whereIn('status', $openStatuses)->sum('total_sell');

        return view('reports.index', compact(
            'startDate', 'endDate', 
            'totalQuotes', 'wonQuotes', 'lostQuotes', 
            'conversionRate', 'totalValueWon', 'totalValueLost', 'pipelineValue'
        ));
    }
}
