@extends('layouts.app')
@section('title','Quotes')
@section('page-title','Quotes')
@section('topbar-actions')
<a href="{{ route('quotes.create') }}" class="btn btn-primary"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;margin-right:4px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> New Quote</a>
@endsection
@section('content')
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden">
    @if(session('success'))
        <div style="padding:16px 20px;background:#f0fdf4;color:#16a34a;border-bottom:1px solid #bbf7d0;font-size:14px">
            {{ session('success') }}
        </div>
    @endif
    
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-weight:700;color:var(--text-primary)">Open Quotes</div>
        <div>
            <input type="text" placeholder="Search quotes..." class="form-control" style="width:250px;font-size:13px;padding:6px 12px">
        </div>
    </div>
    
    <div class="table-wrap">
        @if($quotes->isEmpty())
            <div style="display:flex;flex-direction:column;align-items:center;padding:48px 24px;gap:10px;text-align:center">
                <div style="font-size:36px;opacity:0.25">📋</div>
                <div style="font-size:14px;font-weight:600;color:var(--text-secondary)">No quotes found</div>
                <div style="font-size:13px;color:var(--text-muted)">Get started by creating a new quote record.</div>
                <a href="{{ route('quotes.create') }}" class="btn btn-primary" style="margin-top:8px">+ New Quote</a>
            </div>
        @else
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:#f8fafc;white-space:nowrap">
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Company</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Type</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Customer Name</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Project Name</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Date Request</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Date Due</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Requested By</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600">Status</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;text-align:right">Total Sell</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;text-align:right">Total Cost</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;text-align:right">GM %</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;text-align:right">GM $</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600;text-align:right">Commission</th>
                    <th style="padding:10px 12px;color:var(--text-secondary);font-weight:600"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotes as $quote)
                <tr style="border-bottom:1px solid var(--border);transition:background 0.2s">
                    <td style="padding:12px;font-weight:600">{{ $quote->businessEntity->code ?? 'ESC' }}</td>
                    <td style="padding:12px">{{ $quote->quoteType->code ?? 'SPEC' }}</td>
                    <td style="padding:12px">
                        <a href="{{ route('quotes.show', $quote) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $quote->company->name ?? '—' }}</a>
                        <div style="font-size:10px;color:var(--text-muted)">{{ $quote->quote_number }}</div>
                    </td>
                    <td style="padding:12px;font-weight:600">{{ $quote->project_name ?? '—' }}</td>
                    <td style="padding:12px">{{ $quote->requested_at ? $quote->requested_at->format('d-M') : '—' }}</td>
                    <td style="padding:12px">{{ $quote->due_at ? $quote->due_at->format('d-M') : '—' }}</td>
                    <td style="padding:12px">{{ $quote->assignedUser->name ?? '—' }}</td>
                    <td style="padding:12px">
                        <span class="badge badge-{{ str_replace('_', '-', $quote->status) }}" style="background:#e2e8f0;padding:4px 8px;border-radius:4px;font-size:10px;font-weight:600">{{ strtoupper(str_replace('_', ' ', $quote->status)) }}</span>
                    </td>
                    <td style="padding:12px;text-align:right">${{ number_format($quote->total_sell, 2) }}</td>
                    <td style="padding:12px;text-align:right">${{ number_format($quote->total_cost, 2) }}</td>
                    <td style="padding:12px;text-align:right;background:#eff6ff;font-weight:600">{{ number_format($quote->gross_margin_pct, 2) }}%</td>
                    <td style="padding:12px;text-align:right;background:#eff6ff;font-weight:600">${{ number_format($quote->gross_margin_amount, 2) }}</td>
                    <td style="padding:12px;text-align:right;background:#fef3c7;font-weight:600;color:#d97706">${{ number_format($quote->commission_amount, 2) }}</td>
                    <td style="padding:12px 20px;text-align:right">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-ghost btn-sm" style="font-size:11px;padding:4px 10px;border-radius:4px;color:var(--primary)">View</a>
                            <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-ghost btn-sm" style="font-size:11px;padding:4px 10px;border-radius:4px">Edit</a>
                            <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quote? This cannot be undone.');" style="display:inline-block;margin:0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm" style="font-size:11px;padding:4px 8px;border-radius:4px;color:var(--danger)" title="Delete Quote">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
