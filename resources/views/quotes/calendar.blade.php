@extends('layouts.app')
@section('title', 'Bid Calendar')
@section('page-title', 'Open Pending Bids Calendar')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    .fc {
        background: #fff;
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
    }
    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .fc .fc-button-primary {
        background-color: #fff;
        border-color: var(--border);
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: capitalize;
    }
    .fc .fc-button-primary:not(:disabled):active, 
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:hover {
        background-color: var(--bg-app);
        border-color: var(--border);
        color: var(--text-primary);
    }
    .fc .fc-today-button {
        text-transform: capitalize;
    }
    .fc-theme-standard td, .fc-theme-standard th, .fc-theme-standard .fc-scrollgrid {
        border-color: var(--border-light);
    }
    .fc-day-today {
        background-color: #f8fafc !important;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        border: none;
        border-radius: 4px;
        font-size: 11.5px;
        font-weight: 500;
    }
    /* Event Tooltip styling (using native title for now, could be improved with tippy.js) */
    .fc-event-main {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .legend {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
    }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }
</style>
@endpush

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    
    <div class="legend">
        <div class="legend-item"><div class="legend-color" style="background:#94a3b8"></div> Draft</div>
        <div class="legend-item"><div class="legend-color" style="background:#7c3aed"></div> In Review</div>
        <div class="legend-item"><div class="legend-color" style="background:#0891b2"></div> RFQ / Prep</div>
        <div class="legend-item"><div class="legend-color" style="background:#d97706"></div> Sent / Submitted</div>
    </div>

    <div id="calendar"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var events = {!! json_encode($events) !!};

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            height: 'auto',
            events: events,
            eventDidMount: function(info) {
                // Add a simple tooltip
                let props = info.event.extendedProps;
                let title = info.event.title + '\n' 
                          + 'Status: ' + props.status.replace('_', ' ').toUpperCase() + '\n'
                          + 'Amount: ' + props.amount;
                info.el.setAttribute('title', title);
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            }
        });

        calendar.render();
    });
</script>
@endpush
