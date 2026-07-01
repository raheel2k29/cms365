<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, Quote $quote)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        
        // Store in local storage (storage/app/attachments)
        $path = $file->store('attachments');
        
        $attachment = new Attachment([
            'original_name' => $originalName,
            'stored_name' => basename($path),
            'file_path' => $path,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'description' => $request->input('description'),
            'source' => 'manual',
            'uploaded_by' => auth()->id(),
        ]);
        
        $quote->attachments()->save($attachment);
        
        return redirect()->route('quotes.show', $quote)->with('success', 'File uploaded successfully.');
    }
    
    public function download(Attachment $attachment)
    {
        if (!Storage::exists($attachment->file_path)) {
            abort(404, 'File not found on disk.');
        }
        
        return Storage::download($attachment->file_path, $attachment->original_name);
    }
}
