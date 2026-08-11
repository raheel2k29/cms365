@extends('layouts.app')
@section('title', 'Pipeline')
@section('page-title', 'Sales Pipeline')

@push('styles')
<style>
    .kanban-board {
        display: flex;
        overflow-x: auto;
        gap: 16px;
        padding-bottom: 16px;
        align-items: flex-start;
        min-height: calc(100vh - 150px);
    }
    .kanban-column {
        background: #f8fafc;
        min-width: 320px;
        width: 320px;
        border-radius: 8px;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 180px);
    }
    .kanban-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border-radius: 8px 8px 0 0;
    }
    .kanban-count {
        background: var(--bg-app);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        color: var(--text-secondary);
    }
    .kanban-cards {
        padding: 12px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 100px; /* So empty columns can still be dropped into easily */
    }
    .kanban-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 12px;
        box-shadow: var(--shadow-sm);
        cursor: grab;
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--border-light);
    }
    .kanban-card.sortable-ghost {
        opacity: 0.4;
        background: #e2e8f0;
    }
    .kanban-card-title {
        font-weight: 600;
        font-size: 13.5px;
        color: var(--accent);
        margin-bottom: 4px;
        text-decoration: none;
        display: block;
    }
    .kanban-card-company {
        font-size: 12.5px;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-weight: 500;
    }
    .kanban-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11.5px;
        color: var(--text-muted);
        border-top: 1px dashed var(--border);
        padding-top: 8px;
        margin-top: 4px;
    }
    .kanban-card-amount {
        font-weight: 600;
        color: var(--text-primary);
    }
    .kanban-card-date {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    /* Quick Edit Modal */
    .quick-edit-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15,23,42,0.4);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }
    .quick-edit-modal.active {
        display: flex;
    }
    .quick-edit-content {
        background: #fff;
        border-radius: var(--radius);
        width: 100%;
        max-width: 500px;
        padding: 24px;
        box-shadow: var(--shadow-lg);
    }
    .quick-edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="kanban-board">
    @foreach($statuses as $statusKey => $statusLabel)
        <div class="kanban-column" data-status="{{ $statusKey }}">
            <div class="kanban-header">
                {{ $statusLabel }}
                <span class="kanban-count" id="count-{{ $statusKey }}">{{ isset($quotes[$statusKey]) ? $quotes[$statusKey]->count() : 0 }}</span>
            </div>
            <div class="kanban-cards" id="col-{{ $statusKey }}">
                @if(isset($quotes[$statusKey]))
                    @foreach($quotes[$statusKey] as $quote)
                        <div class="kanban-card" data-id="{{ $quote->id }}">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <a href="{{ route('quotes.show', $quote) }}" class="kanban-card-title">{{ $quote->quote_number }}</a>
                                <button type="button" class="btn-icon btn-quick-edit" data-id="{{ $quote->id }}" data-url="{{ route('quotes.edit', $quote) }}" title="Quick Edit">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            </div>
                            <div class="kanban-card-company">{{ $quote->company ? $quote->company->name : ($quote->project_name ?? 'No Project Name') }}</div>
                            <div class="kanban-card-footer">
                                <div class="kanban-card-amount">${{ number_format($quote->total_sell, 2) }}</div>
                                <div class="kanban-card-date">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $quote->due_at ? $quote->due_at->format('M d, Y') : 'No Date' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const columns = document.querySelectorAll('.kanban-cards');
        
        columns.forEach(col => {
            new Sortable(col, {
                group: 'pipeline',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const itemEl = evt.item;  // dragged HTMLElement
                    const quoteId = itemEl.dataset.id;
                    const newStatus = evt.to.closest('.kanban-column').dataset.status;
                    const oldStatus = evt.from.closest('.kanban-column').dataset.status;
                    
                    if (newStatus !== oldStatus) {
                        // Update counts
                        const fromCountEl = document.getElementById('count-' + oldStatus);
                        const toCountEl = document.getElementById('count-' + newStatus);
                        
                        fromCountEl.innerText = parseInt(fromCountEl.innerText) - 1;
                        toCountEl.innerText = parseInt(toCountEl.innerText) + 1;
                        
                        // Send AJAX request to update DB
                        fetch('{{ route("pipeline.update-status") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                quote_id: quoteId,
                                status: newStatus
                            })
                        }).then(response => {
                            if (!response.ok) {
                                alert("Failed to update status.");
                                // Could revert UI here if needed
                            }
                        });
                    }
                },
            });
        });

        // Quick edit buttons redirect to the actual edit page for now. 
        // We will enhance this with a real modal later once fields are decided!
        document.querySelectorAll('.btn-quick-edit').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = this.dataset.url;
            });
        });
    });
</script>
@endpush
