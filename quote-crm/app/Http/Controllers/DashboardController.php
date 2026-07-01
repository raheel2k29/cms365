<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index()
    {
        $openStatuses = ['new', 'in_review', 'rfq_sent', 'pricing_received', 'quote_prepared', 'quote_sent'];

        $stats = [
            'open'              => Quote::whereIn('status', $openStatuses)->count(),
            'pipeline_value'    => Quote::whereIn('status', $openStatuses)->sum('total_sell'),
            'rfq_sent'          => Quote::where('status', 'rfq_sent')->count(),
            'rfq_value'         => Quote::where('status', 'rfq_sent')->sum('total_sell'),
            'quote_sent'        => Quote::where('status', 'quote_sent')->count(),
            'quote_sent_value'  => Quote::where('status', 'quote_sent')->sum('total_sell'),
            'won_count'         => Quote::where('status', 'won')->count(),
            'won_value'         => Quote::where('status', 'won')->sum('total_sell'),
            'lost_count'        => Quote::where('status', 'lost')->count(),
            'lost_value'        => Quote::where('status', 'lost')->sum('total_sell'),
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
