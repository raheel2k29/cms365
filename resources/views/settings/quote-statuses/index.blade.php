@extends('layouts.app')
@section('title', 'Quote Statuses')
@section('page-title', 'Quote Statuses')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
        <div>
            <h3 style="margin:0; font-size:16px; font-weight:600;">Manage Quote Statuses</h3>
            <p style="margin:4px 0 0 0; color:var(--text-muted); font-size:13px;">Customize the statuses and colors available for your quotes.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').style.display='block'">+ Add Status</button>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:6px; margin-bottom:16px; font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; margin-bottom:16px; font-size:13px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table" id="status-table">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th>Status Name</th>
                    <th>Color Preview</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody id="sortable-list">
                @foreach($statuses as $status)
                <tr data-id="{{ $status->id }}" style="cursor: move;">
                    <td style="color:var(--text-muted);">☰</td>
                    <td style="font-weight:500">{{ $status->name }}</td>
                    <td>
                        <span style="display:inline-block; padding:4px 12px; border-radius:12px; font-size:12px; font-weight:500; background-color:{{ $status->color }};">
                            {{ $status->name }}
                        </span>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">{{ $status->color }}</div>
                    </td>
                    <td style="text-align:right">
                        <button class="btn btn-ghost btn-sm" onclick="openEditModal({{ $status->id }}, '{{ addslashes($status->name) }}', '{{ $status->color }}')">Edit</button>
                        <form action="{{ route('settings.quote-statuses.destroy', $status) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this status?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($statuses->isEmpty())
                <tr>
                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:24px;">No quote statuses found.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color:#fff; margin:10% auto; padding:24px; border-radius:8px; max-width:400px;">
        <h3 style="margin-top:0">Add Quote Status</h3>
        <form action="{{ route('settings.quote-statuses.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:8px; font-weight:500; font-size:13px;">Status Name</label>
                <input type="text" name="name" class="form-control" style="width:100%; padding:10px;" required>
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block; margin-bottom:8px; font-weight:500; font-size:13px;">Color</label>
                <input type="color" name="color" class="form-control" style="width:100%; height:40px; padding:2px;" value="#cbd5e1" required>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color:#fff; margin:10% auto; padding:24px; border-radius:8px; max-width:400px;">
        <h3 style="margin-top:0">Edit Quote Status</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:8px; font-weight:500; font-size:13px;">Status Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" style="width:100%; padding:10px;" required>
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block; margin-bottom:8px; font-weight:500; font-size:13px;">Color</label>
                <input type="color" name="color" id="edit_color" class="form-control" style="width:100%; height:40px; padding:2px;" required>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    function openEditModal(id, name, color) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_color').value = color;
        document.getElementById('editForm').action = '/settings/quote-statuses/' + id;
        document.getElementById('editModal').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('sortable-list');
        if (el) {
            Sortable.create(el, {
                animation: 150,
                onEnd: function (evt) {
                    let order = [];
                    el.querySelectorAll('tr').forEach((tr) => {
                        order.push(tr.getAttribute('data-id'));
                    });
                    
                    fetch('{{ route('settings.quote-statuses.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    });
                }
            });
        }
    });
</script>
@endpush
@endsection
