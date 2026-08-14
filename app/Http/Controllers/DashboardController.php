<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $businessEntityId = auth()->user()->business_entity_id;
        $quoteStatuses = \App\Models\QuoteStatus::where('business_entity_id', $businessEntityId)->get();
        
        $closedNames = ['Won', 'Lost', 'Cancelled', 'No BID', 'Missed'];
        $openStatusIds = $quoteStatuses->filter(fn($s) => !in_array($s->name, $closedNames))->pluck('id')->toArray();
        
        $rfqSentId = $quoteStatuses->firstWhere('name', 'Requested')?->id ?? -1; // Or fallback to old
        $quoteSentId = $quoteStatuses->firstWhere('name', 'Quote Sent')?->id ?? -1;
        $wonId = $quoteStatuses->firstWhere('name', 'Won')?->id ?? -1;
        $lostId = $quoteStatuses->firstWhere('name', 'Lost')?->id ?? -1;

        $stats = [
            'open'              => Quote::where('business_entity_id', $businessEntityId)->whereIn('quote_status_id', $openStatusIds)->count(),
            'pipeline_value'    => Quote::where('business_entity_id', $businessEntityId)->whereIn('quote_status_id', $openStatusIds)->sum('total_sell'),
            'rfq_sent'          => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $rfqSentId)->count(),
            'rfq_value'         => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $rfqSentId)->sum('total_sell'),
            'quote_sent'        => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $quoteSentId)->count(),
            'quote_sent_value'  => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $quoteSentId)->sum('total_sell'),
            'won_count'         => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $wonId)->count(),
            'won_value'         => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $wonId)->sum('total_sell'),
            'lost_count'        => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $lostId)->count(),
            'lost_value'        => Quote::where('business_entity_id', $businessEntityId)->where('quote_status_id', $lostId)->sum('total_sell'),
        ];

        $recentQuotes = Quote::with(['company', 'businessEntity', 'assignedUser', 'emails'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentActivity = ActivityLog::with(['user', 'quote'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        return view('dashboard', compact('stats', 'recentQuotes', 'recentActivity'));
    }
}
