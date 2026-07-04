@extends('layouts.app')
@section('title','Emails')
@section('page-title','Emails')
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Shared Mailbox</h3>
        <span class="text-sm text-gray-500">Auto-synced via Microsoft Graph</span>
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
