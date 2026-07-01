@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('quotes.index') }}" class="btn btn-ghost btn-sm" id="export-csv-btn" style="margin-right:4px">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </a>
    <a href="{{ route('quotes.create') }}" class="btn btn-primary btn-sm" id="new-quote-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + New Quote
    </a>
@endsection

@push('styles')
<style>
    /* ── Date filter bar ──────── */
    .date-bar {
        display: flex; align-items: center; justify-content: flex-end;
        margin-bottom: 18px; gap: 10px;
    }
    .date-bar select { max-width: 200px; }

    /* ── KPI strip ────────────── */
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .kpi-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .kpi-card:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
    .kpi-icon-wrap {
        width: 48px; height: 48px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .kpi-icon-wrap svg { width: 22px; height: 22px; }
    .kpi-icon-wrap.blue   { background: #eff6ff; color: #2563eb; }
    .kpi-icon-wrap.orange { background: #fffbeb; color: #d97706; }
    .kpi-icon-wrap.teal   { background: #ecfeff; color: #0891b2; }
    .kpi-icon-wrap.green  { background: #f0fdf4; color: #16a34a; }
    .kpi-icon-wrap.red    { background: #fef2f2; color: #dc2626; }
    .kpi-label { font-size: 11.5px; color: var(--text-muted); font-weight: 500; margin-bottom: 3px; }
    .kpi-value { font-size: 26px; font-weight: 700; color: var(--text-primary); line-height: 1; }
    .kpi-sub   { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

    /* ── Main grid ────────────── */
    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 18px;
    }

    /* ── Table card ───────────── */
    .table-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
    .table-card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .table-card-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }

    .quote-link { color: var(--accent); text-decoration: none; font-weight: 600; }
    .quote-link:hover { text-decoration: underline; }

    .last-reply { font-size: 12px; }
    .last-reply-time { color: var(--text-muted); font-size: 11px; }

    .view-all-link {
        display: inline-block; padding: 12px 20px;
        font-size: 13px; color: var(--accent); font-weight: 500;
        text-decoration: none;
    }
    .view-all-link:hover { text-decoration: underline; }

    /* ── Right column ─────────── */
    .right-col { display: flex; flex-direction: column; gap: 18px; }

    /* Activity feed */
    .activity-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
    .activity-header { padding: 14px 18px; border-bottom: 1px solid var(--border); font-size: 14px; font-weight: 700; color: var(--text-primary); }
    .activity-list { list-style: none; max-height: 290px; overflow-y: auto; }
    .activity-item { display: flex; gap: 12px; padding: 12px 18px; border-bottom: 1px solid #f8fafc; align-items: flex-start; }
    .activity-item:last-child { border-bottom: none; }
    .activity-icon-wrap {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .activity-icon-wrap svg { width: 14px; height: 14px; }
    .activity-icon-wrap.email  { background: #eff6ff; color: #2563eb; }
    .activity-icon-wrap.quote  { background: #f0fdf4; color: #16a34a; }
    .activity-icon-wrap.attach { background: #fef3c7; color: #d97706; }
    .activity-icon-wrap.note   { background: #f5f3ff; color: #7c3aed; }
    .activity-body { flex: 1; }
    .activity-text { font-size: 12.5px; color: var(--text-secondary); line-height: 1.45; }
    .activity-text strong { color: var(--text-primary); }
    .activity-time { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .view-all-activity { display: block; text-align: center; padding: 10px; font-size: 12.5px; color: var(--accent); text-decoration: none; border-top: 1px solid var(--border); }
    .view-all-activity:hover { background: #fafbfd; }

    /* Conversion card */
    .conversion-card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 18px; }
    .conversion-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
    .donut-wrap { display: flex; align-items: center; gap: 20px; }
    .donut-svg { flex-shrink: 0; }
    .donut-center { font-size: 22px; font-weight: 700; fill: var(--text-primary); }
    .donut-center-sub { font-size: 10px; fill: var(--text-muted); }
    .legend-list { list-style: none; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .legend-label { color: var(--text-secondary); flex: 1; }
    .legend-count { font-weight: 600; color: var(--text-primary); }

    /* Empty state */
    .empty-state { display: flex; flex-direction: column; align-items: center; padding: 48px 24px; gap: 10px; text-align: center; }
    .empty-icon { font-size: 36px; opacity: 0.25; }
    .empty-title { font-size: 14px; font-weight: 600; color: var(--text-secondary); }
    .empty-sub { font-size: 13px; color: var(--text-muted); }
</style>
@endpush

@section('content')

{{-- Date filter --}}
<div class="date-bar">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;color:var(--text-muted)"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/></svg>
    <select class="form-control" style="width:auto;padding:6px 12px;font-size:12.5px" id="date-range-filter">
        <option>Apr 1, 2024 – Apr 30, 2024</option>
        <option>This Month</option>
        <option>Last 30 Days</option>
        <option>This Quarter</option>
        <option>This Year</option>
    </select>
</div>

{{-- KPI strip --}}
<div class="kpi-strip">
    <div class="kpi-card">
        <div class="kpi-icon-wrap blue">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div class="kpi-label">Open Quotes</div>
            <div class="kpi-value">{{ $stats['open'] }}</div>
            <div class="kpi-sub">${{ number_format($stats['pipeline_value'], 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap orange">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="kpi-label">Pending Quotes</div>
            <div class="kpi-value">{{ $stats['rfq_sent'] }}</div>
            <div class="kpi-sub">${{ number_format($stats['rfq_value'], 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap teal">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </div>
        <div>
            <div class="kpi-label">Submitted Quotes</div>
            <div class="kpi-value">{{ $stats['quote_sent'] }}</div>
            <div class="kpi-sub">${{ number_format($stats['quote_sent_value'], 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap green">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="kpi-label">Won Quotes</div>
            <div class="kpi-value">{{ $stats['won_count'] }}</div>
            <div class="kpi-sub">${{ number_format($stats['won_value'], 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap red">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="kpi-label">Lost Quotes</div>
            <div class="kpi-value">{{ $stats['lost_count'] }}</div>
            <div class="kpi-sub">${{ number_format($stats['lost_value'], 0) }}</div>
        </div>
    </div>
</div>

{{-- Main grid --}}
<div class="dash-grid">

    {{-- Recent Quotes table --}}
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">Recent Quotes</div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('reports.index') }}" class="btn btn-ghost btn-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export CSV
                </a>
                <a href="{{ route('quotes.create') }}" class="btn btn-primary btn-sm">+ New Quote</a>
            </div>
        </div>
        <div class="table-wrap">
            @if($recentQuotes->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <div class="empty-title">No quotes yet</div>
                    <div class="empty-sub">Create your first quote to get started</div>
                    <a href="{{ route('quotes.create') }}" class="btn btn-primary" style="margin-top:8px">+ New Quote</a>
                </div>
            @else
            <table>
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Company</th>
                        <th>Project Name</th>
                        <th>Date Request</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Last Reply</th>
                        <th>Total Sell Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentQuotes as $quote)
                    <tr>
                        <td><a href="{{ route('quotes.show', $quote) }}" class="quote-link">{{ $quote->quote_number }}</a></td>
                        <td>{{ $quote->businessEntity->code ?? '—' }}</td>
                        <td class="td-bold">{{ $quote->project_name ?? '—' }}</td>
                        <td>{{ $quote->requested_at?->format('d-M-Y') ?? '—' }}</td>
                        <td>{{ $quote->due_at?->format('d-M-Y') ?? '—' }}</td>
                        <td><span class="badge badge-{{ $quote->status }}">{{ ucfirst(str_replace('_',' ',$quote->status)) }}</span></td>
                        <td>{{ $quote->assignedUser->name ?? '—' }}</td>
                        <td>
                            @if($quote->emails->count())
                                <div class="last-reply">{{ $quote->emails->last()->from_name ?? $quote->emails->last()->from_email }}</div>
                                <div class="last-reply-time">{{ $quote->emails->last()->sent_at?->format('d-M-Y g:i A') }}</div>
                            @else
                                <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                        <td class="td-bold">${{ number_format($quote->total_sell, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <a href="{{ route('quotes.index') }}" class="view-all-link">View all quotes →</a>
            @endif
        </div>
    </div>

    {{-- Right column --}}
    <div class="right-col">

        {{-- Activity feed --}}
        <div class="activity-card">
            <div class="activity-header">Activity Feed</div>
            @if($recentActivity->isEmpty())
                <div class="empty-state" style="padding:24px">
                    <div class="empty-icon">⚡</div>
                    <div class="empty-title">No activity yet</div>
                </div>
            @else
            <ul class="activity-list">
                @foreach($recentActivity as $log)
                <li class="activity-item">
                    <div class="activity-icon-wrap {{ str_contains($log->action,'email') ? 'email' : (str_contains($log->action,'attach') ? 'attach' : (str_contains($log->action,'note') ? 'note' : 'quote')) }}">
                        @if(str_contains($log->action,'email'))
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @elseif(str_contains($log->action,'attach'))
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        @else
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                    <div class="activity-body">
                        <div class="activity-text">{{ $log->description ?? $log->action }}
                            @if($log->quote) <strong>#{{ $log->quote->quote_number }}</strong>@endif
                        </div>
                        <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </li>
                @endforeach
            </ul>
            <a href="#" class="view-all-activity">View all activity →</a>
            @endif
        </div>

        {{-- Quote Conversion --}}
        <div class="conversion-card">
            <div class="conversion-title">Quote Conversion</div>
            @php
                $total = $stats['won_count'] + $stats['lost_count'] + $stats['quote_sent'] + $stats['open'];
                $wonPct  = $total > 0 ? round(($stats['won_count']  / $total) * 100) : 0;
                $subPct  = $total > 0 ? round(($stats['quote_sent'] / $total) * 100) : 0;
                $openPct = $total > 0 ? round(($stats['open']       / $total) * 100) : 0;
                $lostPct = $total > 0 ? round(($stats['lost_count'] / $total) * 100) : 0;
                // SVG donut math
                $r = 42; $cx = 56; $cy = 56; $circ = 2 * pi() * $r;
                $wonDash  = ($circ * $wonPct  / 100); $wonOff  = 0;
                $subDash  = ($circ * $subPct  / 100); $subOff  = -$wonDash;
                $openDash = ($circ * $openPct / 100); $openOff = -($wonDash + $subDash);
                $lostDash = ($circ * $lostPct / 100); $lostOff = -($wonDash + $subDash + $openDash);
            @endphp
            <div class="donut-wrap">
                <svg width="112" height="112" class="donut-svg" viewBox="0 0 112 112">
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#f1f5f9" stroke-width="16"/>
                    @if($wonPct > 0)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#16a34a" stroke-width="16"
                        stroke-dasharray="{{ $wonDash }} {{ $circ - $wonDash }}"
                        stroke-dashoffset="{{ $circ / 4 }}" transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                    @endif
                    @if($subPct > 0)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#2563eb" stroke-width="16"
                        stroke-dasharray="{{ $subDash }} {{ $circ - $subDash }}"
                        stroke-dashoffset="{{ $circ/4 + $wonDash }}" transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                    @endif
                    @if($openPct > 0)
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="#94a3b8" stroke-width="16"
                        stroke-dasharray="{{ $openDash }} {{ $circ - $openDash }}"
                        stroke-dashoffset="{{ $circ/4 + $wonDash + $subDash }}" transform="rotate(-90 {{ $cx }} {{ $cy }})"/>
                    @endif
                    <text x="{{ $cx }}" y="{{ $cy - 4 }}" text-anchor="middle" class="donut-center" font-size="18" font-weight="700" fill="#0f172a">{{ $wonPct }}%</text>
                    <text x="{{ $cx }}" y="{{ $cy + 12 }}" text-anchor="middle" class="donut-center-sub" font-size="9" fill="#94a3b8">Win Rate</text>
                </svg>
                <ul class="legend-list">
                    <li class="legend-item"><div class="legend-dot" style="background:#16a34a"></div><span class="legend-label">Quotes Won</span><span class="legend-count">{{ $stats['won_count'] }} ({{ $wonPct }}%)</span></li>
                    <li class="legend-item"><div class="legend-dot" style="background:#2563eb"></div><span class="legend-label">Submitted</span><span class="legend-count">{{ $stats['quote_sent'] }} ({{ $subPct }}%)</span></li>
                    <li class="legend-item"><div class="legend-dot" style="background:#94a3b8"></div><span class="legend-label">Open</span><span class="legend-count">{{ $stats['open'] }} ({{ $openPct }}%)</span></li>
                    <li class="legend-item"><div class="legend-dot" style="background:#dc2626"></div><span class="legend-label">Lost</span><span class="legend-count">{{ $stats['lost_count'] }} ({{ $lostPct }}%)</span></li>
                </ul>
            </div>
            <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted);text-align:center">
                Total Quotes Received: <strong style="color:var(--text-primary)">{{ $total }}</strong>
            </div>
        </div>

    </div>
</div>
@endsection
