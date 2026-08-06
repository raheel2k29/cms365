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

    public function export(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfMonth();

        $quotes = Quote::with(['businessEntity', 'assignedUser'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "quotes_export_" . $startDate->format('Y-m-d') . "_to_" . $endDate->format('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Quote Number', 'Project Name', 'Company Code', 'Status', 
            'Assigned To', 'Date Created', 'Date Due', 
            'Total Cost ($)', 'Total Sell ($)', 'Gross Margin ($)', 'Margin (%)'
        ];

        $callback = function() use($quotes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($quotes as $quote) {
                $cost = $quote->total_cost ?? 0;
                $sell = $quote->total_sell ?? 0;
                $margin = $sell - $cost;
                $marginPct = $sell > 0 ? round(($margin / $sell) * 100, 2) : 0;

                fputcsv($file, [
                    $quote->quote_number,
                    $quote->project_name,
                    $quote->businessEntity->code ?? '—',
                    $quote->status,
                    $quote->assignedUser->name ?? '—',
                    $quote->created_at->format('Y-m-d'),
                    $quote->due_at ? $quote->due_at->format('Y-m-d') : '—',
                    number_format($cost, 2, '.', ''),
                    number_format($sell, 2, '.', ''),
                    number_format($margin, 2, '.', ''),
                    $marginPct . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
