<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index()
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

        // Fetch open quotes grouped by status
        $quotes = Quote::with(['company'])
            ->whereIn('status', array_keys($statuses))
            ->orderBy('due_at', 'asc')
            ->get()
            ->groupBy('status');

        return view('pipeline.index', compact('quotes', 'statuses'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'status' => 'required|string'
        ]);

        $quote = Quote::findOrFail($request->quote_id);
        
        if ($quote->status !== $request->status) {
            $oldStatus = $quote->status;
            $quote->status = $request->status;
            $quote->save();
            
            $quote->activityLogs()->create([
                'user_id' => auth()->id(),
                'action'  => 'status_changed',
                'description' => "Status changed from {$oldStatus} to {$quote->status} via Pipeline."
            ]);
        }

        return response()->json(['success' => true]);
    }
}
