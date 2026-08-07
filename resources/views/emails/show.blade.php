@extends('layouts.app')
@section('title', 'View Email')
@section('page-title', 'Email Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('emails.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
        &larr; Back to Inbox
    </a>
</div>

<div class="card bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">{{ $email->subject ?? '(No Subject)' }}</h2>
            <div>
                @if($email->quote_id)
                    <a href="{{ route('quotes.show', $email->quote_id) }}" class="btn btn-primary" style="background:var(--accent); color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:13px; font-weight:600">
                        View Quote #{{ $email->quote->quote_number ?? $email->quote_id }}
                    </a>
                @else
                    <a href="{{ route('quotes.create', ['email_id' => $email->id]) }}" class="btn btn-success" style="background:#10b981; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:13px; font-weight:600">
                        + Create Quote from Email
                    </a>
                @endif
            </div>
        </div>
        
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-900 font-medium">{{ $email->from_name }} &lt;{{ $email->from_email }}&gt;</p>
                <p class="text-xs text-gray-500 mt-1">To: {{ $email->to_email }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">{{ $email->sent_at ? $email->sent_at->format('F j, Y g:i A') : 'Unknown Date' }}</p>
                @if($email->has_attachments)
                <span class="inline-block mt-2 px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded-md">
                    📎 Has Attachments
                </span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="p-6 prose max-w-none text-gray-800" style="min-height: 300px;">
        @if($email->body_html)
            @if(strip_tags($email->body_html) === $email->body_html)
                {{-- No HTML tags detected, treat as plain text --}}
                {!! nl2br(e($email->body_html)) !!}
            @else
                {!! $email->body_html !!}
            @endif
        @else
            {!! nl2br(e($email->body_text)) !!}
        @endif
    </div>

    @if($email->attachments->isNotEmpty())
    <div class="p-6 bg-gray-50 border-t border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Attachments</h3>
        <ul class="space-y-2">
            @foreach($email->attachments as $att)
            <li class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <a href="{{ route('attachments.download', $att) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    {{ $att->original_name }}
                </a>
                <span class="text-xs text-gray-500">({{ number_format($att->file_size / 1024, 1) }} KB)</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
