@extends('layouts.app')
@section('title','Emails')
@section('page-title','Emails')
@section('topbar-actions')
    <button onclick="syncEmails(this)" class="btn btn-primary btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;margin-right:4px;display:inline-block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Sync Now
    </button>
    <script>
        function syncEmails(btn) {
            btn.innerHTML = 'Syncing...';
            btn.disabled = true;
            fetch('/api/sync-emails')
                .then(r => r.json())
                .then(data => {
                    window.location.reload();
                })
                .catch(e => {
                    alert('Error syncing emails');
                    btn.innerHTML = 'Sync Now';
                    btn.disabled = false;
                });
        }
    </script>
@endsection

@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Shared Mailbox</h3>
        <span class="text-sm text-gray-500">Auto-syncs every minute (or click Sync Now)</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50 text-gray-500">
                <tr>
                    <th scope="col" class="px-6 py-4">From</th>
                    <th scope="col" class="px-6 py-4">Subject</th>
                    <th scope="col" class="px-6 py-4">Date</th>
                    <th scope="col" class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($emails as $email)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $email->from_name }}</div>
                        <div class="text-gray-500 text-xs">{{ $email->from_email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900 truncate max-w-md">{{ $email->subject ?? '(No Subject)' }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $email->sent_at ? $email->sent_at->format('M d, Y h:i A') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('emails.show', $email->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <div style="font-size:36px;margin-bottom:12px">📭</div>
                        <p>No emails synced yet.</p>
                        <p class="text-xs mt-2">Run <code>php artisan emails:sync</code> to pull messages.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($emails->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $emails->links() }}
    </div>
    @endif
</div>
@endsection
