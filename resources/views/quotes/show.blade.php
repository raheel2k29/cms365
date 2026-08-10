@extends('layouts.app')
@section('title', 'Quote ' . $quote->quote_number)
@section('page-title', 'Quote: ' . $quote->quote_number)
@section('topbar-actions')
<a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-ghost" style="margin-right:8px" target="_blank"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Download PDF</a>
<a href="{{ route('quotes.edit', $quote) }}" class="btn btn-ghost" style="margin-right:8px"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Edit Quote</a>

<button type="button" class="btn btn-primary" onclick="document.getElementById('sendQuoteModal').style.display='block'" style="background:#16a34a; border-color:#16a34a; display:inline-flex; align-items:center;">
    <svg style="width:20px;height:20px;margin-right:8px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> 
    Send to Customer
</button>
@endsection

@push('styles')
<style>
    .record-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); font-weight: 700; color: var(--text-primary); display: flex; justify-content: space-between; align-items: center; }
    .card-body { padding: 20px; }
    
    .status-bar { display: flex; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-sm); }
    .status-step { flex: 1; text-align: center; padding: 14px 10px; font-size: 12px; font-weight: 600; color: var(--text-muted); position: relative; border-right: 1px solid var(--border); }
    .status-step:last-child { border-right: none; }
    .status-step.active { background: var(--accent-soft); color: var(--accent); }
    
    .timeline { padding-left: 10px; border-left: 2px solid var(--border); margin-left: 10px; }
    .timeline-item { position: relative; margin-bottom: 20px; }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-dot { position: absolute; left: -17px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #fff; border: 2px solid var(--accent); }
    .timeline-content { background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 13px; }
    .timeline-date { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
    
    .tabs { display: flex; border-bottom: 1px solid var(--border); background: #f8fafc; }
    .tab { padding: 12px 20px; font-size: 13px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s; }
    .tab:hover { color: var(--text-primary); }
    .tab.active { color: var(--accent); border-bottom-color: var(--accent); background: #fff; }
    .tab-content { display: none; padding: 20px; }
    .tab-content.active { display: block; }
    
    .email-thread { display: flex; flex-direction: column; gap: 16px; max-height: 500px; overflow-y: auto; padding-right: 8px; }
    .email-bubble { max-width: 85%; padding: 14px; border-radius: 12px; font-size: 13px; line-height: 1.5; border: 1px solid var(--border-light); }
    .email-inbound { align-self: flex-start; background: #f1f5f9; border-bottom-left-radius: 4px; }
    .email-outbound { align-self: flex-end; background: #eff6ff; border-color: #bfdbfe; border-bottom-right-radius: 4px; }
    .email-meta { display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 8px; }
    .email-subject { font-weight: 600; margin-bottom: 6px; color: var(--text-primary); }
    .email-body { overflow-wrap: break-word; }
    .email-body iframe { border: none; width: 100%; height: 300px; background: #fff; border-radius: 4px; margin-top: 8px; }
</style>
@endpush

@section('content')
<div class="status-bar">
    @php
        $flow = ['new', 'in_review', 'quote_sent', 'won'];
        $currentIdx = array_search($quote->status, $flow);
    @endphp
    @foreach($flow as $idx => $st)
        <div class="status-step {{ $idx <= $currentIdx ? 'active' : '' }}">
            {{ ucwords(str_replace('_', ' ', $st)) }}
        </div>
    @endforeach
</div>

<div class="record-grid">
    <div>
        <div class="card">
            <div class="card-header">Quote Details</div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;font-size:13px">
                    <div>
                        <div style="color:var(--text-muted);margin-bottom:4px">Project Name</div>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $quote->project_name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="color:var(--text-muted);margin-bottom:4px">Customer</div>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $quote->company->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="color:var(--text-muted);margin-bottom:4px">Contact</div>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $quote->contact->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div style="color:var(--text-muted);margin-bottom:4px">Assigned To</div>
                        <div style="font-weight:600;color:var(--text-primary)">{{ $quote->assignedUser->name ?? '—' }}</div>
                    </div>
                </div>

                <div style="border-top:1px solid var(--border);padding-top:20px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <div style="font-weight:700">Line Items</div>
                        @if($quote->items->isNotEmpty())
                        <a href="{{ route('quotes.vendor-requests.create', $quote) }}" class="btn btn-outline-primary btn-sm" style="font-size:12px;padding:4px 8px;border:1px solid var(--accent);color:var(--accent);text-decoration:none;border-radius:4px;background:#fff">
                            📨 Request Vendor Pricing
                        </a>
                        @endif
                    </div>
                    <table style="width:100%;font-size:13px;text-align:left;border-collapse:collapse">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border)">
                                <th style="padding:8px 0;color:var(--text-muted)">Description</th>
                                <th style="padding:8px 0;color:var(--text-muted)">Qty</th>
                                <th style="padding:8px 0;color:var(--text-muted)">Unit Price</th>
                                <th style="padding:8px 0;color:var(--text-muted);text-align:right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quote->items as $item)
                            <tr style="border-bottom:1px solid var(--border-light)">
                                <td style="padding:12px 0">
                                    {{ $item->description }}
                                    @if($item->spec_sheet_url)
                                        <div style="margin-top: 4px;">
                                            <a href="{{ $item->spec_sheet_url }}" target="_blank" style="font-size: 11px; color: var(--accent); text-decoration: none;">📄 Spec Sheet</a>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 0">{{ $item->qty }}</td>
                                <td style="padding:12px 0">${{ number_format($item->sell_price, 2) }}</td>
                                <td style="padding:12px 0;text-align:right;font-weight:600">${{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                            @if($quote->items->isEmpty())
                            <tr>
                                <td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted)">No items found. Edit quote to add items.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <div style="display:flex;justify-content:flex-end;margin-top:16px;font-size:16px">
                        <div>
                            <div style="display:flex;justify-content:space-between;gap:40px;margin-bottom:4px;font-size:13px"><span style="color:var(--text-muted)">Total Cost:</span> <strong>${{ number_format($quote->total_cost, 2) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;gap:40px;margin-bottom:4px"><span style="color:var(--text-muted)">Total Sell:</span> <strong>${{ number_format($quote->total_sell, 2) }}</strong></div>
                            <div style="display:flex;justify-content:space-between;gap:40px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);color:#16a34a"><span style="font-size:12px;font-weight:600">COMMISSION:</span> <strong>${{ number_format($quote->commission_amount, 2) }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Attachments
            </div>
            <div class="card-body">
                <form action="{{ route('quotes.attachments.store', $quote) }}" method="POST" enctype="multipart/form-data" style="margin-bottom:16px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid var(--border-light)">
                    @csrf
                    <div style="display:flex; gap:12px; align-items:flex-end">
                        <div style="flex:1">
                            <label style="font-size:11px;font-weight:600;color:var(--text-muted)">File</label>
                            <input type="file" name="file" class="form-control" required style="font-size:13px; padding:6px">
                        </div>
                        <div style="flex:2">
                            <label style="font-size:11px;font-weight:600;color:var(--text-muted)">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Optional description..." style="font-size:13px; padding:6px">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm" style="padding:6px 12px">Upload</button>
                        </div>
                    </div>
                </form>
                @if($quote->attachments->isEmpty())
                <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:12px">No attachments yet. Drop files here or upload.</div>
                @else
                <ul style="list-style:none">
                    @foreach($quote->attachments as $att)
                    <li style="display:flex;align-items:center;justify-content:space-between;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:8px">
                        <div style="display:flex;align-items:center;gap:10px;font-size:13px">
                            <svg style="width:20px;height:20px;color:var(--text-muted)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <div>
                                <div>{{ $att->original_name }}</div>
                                @if($att->description)
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $att->description }}</div>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('attachments.download', $att) }}" class="btn btn-ghost btn-sm" target="_blank">Download</a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header" style="padding:0; border-bottom:none">
                <div class="tabs">
                    <div class="tab active" onclick="switchTab('customer')">Customer Thread</div>
                    <div class="tab" onclick="switchTab('vendor')">Vendor Thread</div>
                    <div class="tab" onclick="switchTab('internal')">Internal Notes</div>
                </div>
            </div>
            
            <div id="tab-customer" class="tab-content active">
                <div style="display:flex; justify-content:flex-end; margin-bottom:12px">
                    <button type="button" onclick="syncEmailsQuote(this)" class="btn btn-outline-primary btn-sm" style="font-size:12px; padding:4px 8px; border:1px solid var(--border); background:#fff; border-radius:4px">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;margin-right:4px;display:inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sync New Replies
                    </button>
                </div>
                <div class="email-thread">
                    @php $customerEmails = $quote->emails->where('thread_type', 'customer'); @endphp
                    @if($customerEmails->isEmpty())
                        <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:24px">No customer emails found for this quote.</div>
                    @else
                        @foreach($customerEmails as $email)
                        <div class="email-bubble {{ $email->direction === 'inbound' ? 'email-inbound' : 'email-outbound' }}">
                            <div class="email-meta">
                                <div><strong>{{ $email->from_name ?: $email->from_email }}</strong> to {{ $email->to_email }}</div>
                                <div>{{ $email->sent_at ? $email->sent_at->format('M d, g:i A') : $email->created_at->format('M d, g:i A') }}</div>
                            </div>
                            <div class="email-subject">{{ $email->subject }}</div>
                            <div class="email-body">
                                @if($email->body_html)
                                    <iframe srcdoc="{{ $email->body_html }}" style="border:none; width:100%; height:auto; min-height:150px"></iframe>
                                @else
                                    {!! nl2br(e($email->body_text)) !!}
                                @endif
                            </div>
                            @if($email->attachments->isNotEmpty())
                            <div style="margin-top:12px; padding-top:12px; border-top:1px solid rgba(0,0,0,0.1)">
                                <div style="font-size:11px; font-weight:600; margin-bottom:4px">Attachments:</div>
                                <ul style="list-style:none; padding:0; margin:0; font-size:12px">
                                @foreach($email->attachments as $att)
                                    <li><a href="{{ route('attachments.download', $att) }}" target="_blank" style="color:var(--accent);text-decoration:none">📎 {{ $att->original_name }}</a></li>
                                @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
                
                <div style="padding:16px; border-top:1px solid var(--border); background:#f8fafc">
                    <form action="{{ route('quotes.reply', $quote) }}" method="POST">
                        @csrf
                        <input type="hidden" name="thread_type" value="customer">
                        <input type="hidden" name="to_email" value="{{ $quote->contact->email }}">
                        <div style="margin-bottom:8px">
                            <textarea name="message" class="form-control" rows="3" placeholder="Reply to customer..." required style="width:100%; padding:10px; font-size:13px"></textarea>
                        </div>
                        <div style="text-align:right">
                            <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="tab-vendor" class="tab-content">
                <div class="email-thread">
                    @php $vendorEmails = $quote->emails->where('thread_type', 'vendor'); @endphp
                    @if($vendorEmails->isEmpty())
                        <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:24px">No vendor emails found for this quote.</div>
                    @else
                        @foreach($vendorEmails as $email)
                        <div class="email-bubble {{ $email->direction === 'inbound' ? 'email-inbound' : 'email-outbound' }}">
                            <div class="email-meta">
                                <div><strong>{{ $email->from_name ?: $email->from_email }}</strong> to {{ $email->to_email }}</div>
                                <div>{{ $email->sent_at ? $email->sent_at->format('M d, g:i A') : $email->created_at->format('M d, g:i A') }}</div>
                            </div>
                            <div class="email-subject">{{ $email->subject }}</div>
                            <div class="email-body">
                                @if($email->body_html)
                                    <iframe srcdoc="{{ $email->body_html }}" style="border:none; width:100%; height:auto; min-height:150px"></iframe>
                                @else
                                    {!! nl2br(e($email->body_text)) !!}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div id="tab-internal" class="tab-content">
                <form action="{{ route('quotes.notes.store', $quote) }}" method="POST" style="margin-bottom:16px">
                    @csrf
                    <textarea name="body" class="form-control" rows="3" placeholder="Add a note to this quote thread..." required></textarea>
                    <div style="text-align:right;margin-top:8px">
                        <button type="submit" class="btn btn-primary btn-sm">Post Note</button>
                    </div>
                </form>
                @foreach($quote->notes as $note)
                <div style="margin-bottom: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; font-size: 13px; border: 1px solid var(--border-light)">
                    <div style="display:flex; justify-content: space-between; margin-bottom: 8px; font-size: 11px; color: var(--text-muted)">
                        <strong>{{ $note->user->name ?? 'System' }}</strong>
                        <span>{{ $note->created_at->format('M d, g:i A') }}</span>
                    </div>
                    <div>{!! nl2br(e($note->body)) !!}</div>
                </div>
                @endforeach
            </div>
        </div>

        <script>
            function switchTab(tabId) {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                event.currentTarget.classList.add('active');
                document.getElementById('tab-' + tabId).classList.add('active');
            }
            
            function syncEmailsQuote(btn) {
                btn.innerHTML = 'Syncing...';
                btn.disabled = true;
                fetch('/api/sync-emails')
                    .then(r => r.json())
                    .then(data => {
                        window.location.reload();
                    })
                    .catch(e => {
                        alert('Error syncing emails');
                        btn.innerHTML = 'Sync New Replies';
                        btn.disabled = false;
                    });
            }
        </script>

        <div class="card">
            <div class="card-header">Quote Timeline</div>
            <div class="card-body" style="padding-top:12px">
                <div class="timeline">
                    @foreach($quote->activityLogs as $log)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">{{ $log->created_at->format('M d, Y g:i A') }}</div>
                            <div style="color:var(--text-primary)">{!! $log->description !!}</div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:6px">— {{ $log->user->name ?? 'System' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sendQuoteModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
  <div class="modal-content" style="background-color:#fefefe; margin:10% auto; padding:24px; border:1px solid #888; max-width:600px; border-radius:8px;">
    <h3 style="margin-top:0">Send Quote PDF to {{ $quote->contact->email ?? 'Customer' }}</h3>
    <form action="{{ route('quotes.send-email', $quote) }}" method="POST">
        @csrf
        @php
            $outboundCount = $quote->emails->where('thread_type', 'customer')->where('direction', 'outbound')->count();
            if ($outboundCount == 0) {
                $defaultSubject = "Your Quote is Ready: {$quote->project_name}";
            } else {
                $defaultSubject = "Updated Quote (v" . ($outboundCount + 1) . "): {$quote->project_name}";
            }
        @endphp
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:8px">Subject:</label>
            <input type="text" name="subject" class="form-control" value="{{ $defaultSubject }}" style="width:100%; padding:12px; font-size:14px; border-radius:4px; border:1px solid var(--border);" required>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-weight:600; display:block; margin-bottom:8px">Email Message:</label>
            <textarea name="message" class="form-control" rows="8" style="width:100%; padding:12px; font-size:14px; border-radius:4px; border:1px solid var(--border);" required>Hello {{ $quote->contact->name ?? 'Customer' }},

Please find your finalized quote attached.

Let us know if you have any questions!


Thank you,
{{ auth()->user()->name ?? 'Quote CRM Team' }}</textarea>
        </div>
        <div style="text-align:right">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('sendQuoteModal').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;">Send Email with PDF</button>
        </div>
    </form>
  </div>
</div>
@endsection
