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
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ $email->subject ?? '(No Subject)' }}</h2>
        
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
            {!! $email->body_html !!}
        @else
            {!! nl2br(e($email->body_text)) !!}
        @endif
    </div>
</div>
@endsection
