@extends('layouts.app')
@section('title', 'Reports Dashboard')
@section('page-title', 'Reports Dashboard')
@section('topbar-actions')
<form action="{{ route('reports.index') }}" method="GET" style="display:flex;gap:12px;align-items:center;margin:0">
    <div style="display:flex;align-items:center;gap:8px">
        <label style="font-size:12px;color:var(--text-muted)">From:</label>
        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control" style="font-size:12px;padding:6px 12px;width:auto">
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <label style="font-size:12px;color:var(--text-muted)">To:</label>
        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control" style="font-size:12px;padding:6px 12px;width:auto">
    </div>
    <button type="submit" class="btn btn-primary" style="padding:6px 16px;font-size:12px">Apply</button>
</form>
@endsection

@push('styles')
<style>
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 24px; }
    .kpi-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: 8px; }
    .kpi-title { font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-value { font-size: 32px; font-weight: 700; color: var(--text-primary); }
    
    .chart-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
    .chart-header { padding: 16px 24px; border-bottom: 1px solid var(--border); font-weight: 700; color: var(--text-primary); }
    .chart-body { padding: 32px 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px; }
    
    .progress-bar-container { width: 100%; height: 24px; background: #fee2e2; border-radius: 12px; overflow: hidden; display: flex; }
    .progress-bar-fill { height: 100%; background: #22c55e; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: bold; transition: width 0.5s ease; }
</style>
@endpush

@section('content')
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-title">Total Quotes Created</div>
        <div class="kpi-value">{{ number_format($totalQuotes) }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Quote Win Rate</div>
        <div class="kpi-value" style="color: {{ $conversionRate >= 50 ? '#22c55e' : '#eab308' }}">{{ number_format($conversionRate, 1) }}%</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Total Value Won</div>
        <div class="kpi-value" style="color:#22c55e">${{ number_format($totalValueWon, 2) }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-title">Total Value Lost</div>
        <div class="kpi-value" style="color:#ef4444">${{ number_format($totalValueLost, 2) }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="chart-card">
        <div class="chart-header">Sales Summarization (Won vs. Lost)</div>
        <div class="chart-body">
            @php 
                $totalResolvedValue = $totalValueWon + $totalValueLost;
                $wonPct = $totalResolvedValue > 0 ? ($totalValueWon / $totalResolvedValue) * 100 : 0;
            @endphp
            
            @if($totalResolvedValue == 0)
                <div style="color:var(--text-muted);font-size:14px;padding:24px 0">No resolved quotes in this period.</div>
            @else
                <div style="width:100%;max-width:500px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;font-weight:600">
                        <span style="color:#16a34a">Won: ${{ number_format($totalValueWon) }}</span>
                        <span style="color:#dc2626">Lost: ${{ number_format($totalValueLost) }}</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: {{ $wonPct }}%">
                            @if($wonPct > 10){{ number_format($wonPct, 1) }}%@endif
                        </div>
                    </div>
                    <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--text-muted)">
                        Total Resolved Volume: <strong>${{ number_format($totalResolvedValue, 2) }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">Open Pipeline Value</div>
        <div class="chart-body">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:-16px;text-transform:uppercase;font-weight:600">Total Potential Revenue</div>
            <div style="font-size:48px;font-weight:800;color:var(--accent)">
                ${{ number_format($pipelineValue, 2) }}
            </div>
            <div style="font-size:13px;color:var(--text-muted);text-align:center">
                This is the total value of all quotes currently in New, Pricing Received, In Review, or Sent status during this period.
            </div>
        </div>
    </div>
</div>
@endsection
