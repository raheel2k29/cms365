<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Company;
use App\Models\Contact;
use App\Models\QuoteType;
use App\Models\BusinessEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with(['company', 'contact', 'assignedUser', 'quoteType'])
            ->orderByDesc('created_at')
            ->get();
            
        return view('quotes.index', compact('quotes'));
    }

    public function create(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $contacts = Contact::orderBy('name')->get();
        $quoteTypes = QuoteType::where('is_active', true)->get();
        
        $emailId = $request->query('email_id');
        $email = null;
        if ($emailId) {
            $email = \App\Models\Email::find($emailId);
        }
        
        return view('quotes.create', compact('companies', 'contacts', 'quoteTypes', 'email'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'nullable|string|max:255',
            'project_address' => 'nullable|string|max:255',
            'due_at'       => 'nullable|date',
            'expires_at'   => 'nullable|date',
            'company_id'   => 'nullable|exists:companies,id',
            'contact_id'   => 'nullable|exists:contacts,id',
            'quote_type_id'=> 'nullable|exists:quote_types,id',
            'email_id'     => 'nullable|exists:emails,id',
        ]);

        $businessEntity = BusinessEntity::where('code', 'ESC')->first() ?? BusinessEntity::first();

        $quote = new Quote($request->except('email_id'));
        $quote->quote_number = Quote::generateNumber();
        $quote->business_entity_id = $businessEntity->id;
        $quote->assigned_to = auth()->id();
        $quote->status = 'new';
        $quote->save();

        // If created from an email, link the email and the whole thread to this quote
        if ($request->filled('email_id')) {
            $email = \App\Models\Email::find($request->email_id);
            if ($email) {
                $email->quote_id = $quote->id;
                $email->save();
                
                // Also update any other emails in the same conversation
                if ($email->conversation_id) {
                    \App\Models\Email::where('conversation_id', $email->conversation_id)
                                     ->update(['quote_id' => $quote->id]);
                }
            }
        }

        // Log creation
        $quote->activityLogs()->create([
            'user_id' => auth()->id(),
            'action'  => 'created',
            'description' => 'Quote created.'
        ]);

        return redirect()->route('quotes.edit', $quote)->with('success', 'Quote initialized. Please build your line items.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['company', 'contact', 'assignedUser', 'items', 'attachments', 'emails.attachments', 'notes.user', 'activityLogs.user']);
        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $quote->load('items');
        $companies = Company::orderBy('name')->get();
        $contacts = Contact::orderBy('name')->get();
        $quoteTypes = QuoteType::where('is_active', true)->get();
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        
        return view('quotes.edit', compact('quote', 'companies', 'contacts', 'quoteTypes', 'vendors'));
    }

    public function update(Request $request, Quote $quote)
    {
        // This method will handle updating the quote metadata AND saving items from the dynamic builder
        $validated = $request->validate([
            'project_name' => 'nullable|string|max:255',
            'project_address' => 'nullable|string|max:255',
            'due_at'       => 'nullable|date',
            'expires_at'   => 'nullable|date',
            'company_id'   => 'nullable|exists:companies,id',
            'contact_id'   => 'nullable|exists:contacts,id',
            'quote_type_id'=> 'nullable|exists:quote_types,id',
            'status'       => 'nullable|string',
            'items'        => 'nullable|array',
            'items.*.description' => 'nullable|string',
            'items.*.spec_sheet_url' => 'nullable|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.cost_price'  => 'required|numeric|min:0',
            'items.*.sell_price'  => 'required|numeric|min:0',
            'items.*.type'        => 'nullable|string',
            'items.*.rep'         => 'nullable|string',
            'items.*.vendor_id'   => 'nullable|exists:vendors,id',
            'items.*.unit'        => 'nullable|string',
            'items.*.quoted_by'   => 'nullable|string',
            'items.*.line_note'   => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $quote, $validated) {
            $oldStatus = $quote->status;
            
            $quote->fill($request->only(['project_name', 'project_address', 'due_at', 'expires_at', 'company_id', 'contact_id', 'quote_type_id']));
            if ($request->has('status') && $request->status !== $quote->status) {
                $quote->status = $request->status;
                $quote->activityLogs()->create([
                    'user_id' => auth()->id(),
                    'action'  => 'status_changed',
                    'description' => "Status changed from {$oldStatus} to {$quote->status}."
                ]);
            }
            
            // Sync Items
            if ($request->has('items')) {
                $quote->items()->delete(); // simplify by replacing
                foreach ($validated['items'] as $index => $itemData) {
                    $specSheetUrl = $itemData['spec_sheet_url'] ?? null;
                    if ($specSheetUrl && !preg_match("~^(?:f|ht)tps?://~i", $specSheetUrl)) {
                        $specSheetUrl = "https://" . $specSheetUrl;
                    }
                    
                    $lineTotal = $itemData['qty'] * $itemData['sell_price'];
                    $marginPct = $itemData['sell_price'] > 0 ? (($itemData['sell_price'] - $itemData['cost_price']) / $itemData['sell_price']) * 100 : 0;
                    
                    $quote->items()->create([
                        'sort_order' => $index,
                        'description' => $itemData['description'] ?? 'Item',
                        'spec_sheet_url' => $specSheetUrl,
                        'qty' => $itemData['qty'],
                        'cost_price' => $itemData['cost_price'],
                        'sell_price' => $itemData['sell_price'],
                        'line_total' => $lineTotal,
                        'margin_pct' => $marginPct,
                        'type' => $itemData['type'] ?? null,
                        'rep' => $itemData['rep'] ?? null,
                        'vendor_id' => $itemData['vendor_id'] ?? null,
                        'unit' => $itemData['unit'] ?? null,
                        'quoted_by' => $itemData['quoted_by'] ?? null,
                        'line_note' => $itemData['line_note'] ?? null,
                    ]);
                }
            }
            
            $quote->calculateTotals();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'quote' => $quote->fresh(['items'])]);
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote updated successfully.');
    }

    public function pdf(Quote $quote)
    {
        $quote->load(['company', 'contact', 'items.vendor']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotes.pdf', compact('quote'));
        return $pdf->download("Quote_{$quote->quote_number}.pdf");
    }

    public function sendEmail(Request $request, Quote $quote)
    {
        // 1. Ensure the quote has a contact with an email
        if (!$quote->contact || !$quote->contact->email) {
            return redirect()->back()->with('error', 'Cannot send email: This quote does not have a Contact with a valid email address.');
        }

        // 2. Generate the PDF
        $quote->load(['company', 'contact', 'items.vendor']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quotes.pdf', compact('quote'));
        
        // 3. Save PDF temporarily
        $fileName = "Quote_{$quote->quote_number}.pdf";
        $tempPath = storage_path("app/public/temp_{$fileName}");
        $pdf->save($tempPath);

        // 4. Send Email via MS Graph
        $outlookService = new \App\Services\OutlookService();
        $toEmail = $quote->contact->email;
        $subject = $request->input('subject', "Your Quote is Ready: {$quote->project_name}");
        $ccEmails = array_filter(array_map('trim', explode(',', $request->input('cc_emails', ''))));
        
        if ($request->has('message')) {
            $htmlContent = nl2br(e($request->input('message')));
        } else {
            $htmlContent = "
                <p>Hello {$quote->contact->name},</p>
                <p>Please find your finalized quote attached.</p>
                <p>Let us know if you have any questions!</p>
                <br>
                <p>Thank you,<br>" . (auth()->user()->name ?? 'Quote CRM Team') . "</p>
            ";
        }

        $success = $outlookService->sendEmail($toEmail, $subject, $htmlContent, [$tempPath], $ccEmails);

        if ($success) {
            // Save the email to the database so it shows up in the thread
            $email = $quote->emails()->create([
                'thread_type' => 'customer',
                'direction'   => 'outbound',
                'from_email'  => env('SHARED_MAILBOX_ADDRESS', 'sales@electricsupplyconnections.com'),
                'to_email'    => $toEmail,
                'cc_emails'   => implode(', ', $ccEmails),
                'subject'     => $subject,
                'body_html'   => $htmlContent,
                'body_text'   => strip_tags(str_replace('<br>', "\n", $htmlContent)),
                'has_attachments' => true,
                'sent_at'     => now(),
            ]);

            // Save the PDF as an attachment in the database
            if (file_exists($tempPath)) {
                $storedName = \Illuminate\Support\Str::uuid() . '_' . $fileName;
                $filePath = 'attachments/' . $storedName;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, file_get_contents($tempPath));
                
                \App\Models\Attachment::create([
                    'quote_id' => $quote->id,
                    'email_id' => $email->id,
                    'original_name' => $fileName,
                    'stored_name' => $storedName,
                    'file_path' => $filePath,
                    'mime_type' => 'application/pdf',
                    'file_size' => filesize($tempPath),
                    'source' => 'system_generated'
                ]);
                
                unlink($tempPath); // Clean up temp file
            }

            // Log activity and update status
            $quote->activityLogs()->create([
                'user_id' => auth()->id(),
                'action'  => 'status_changed',
                'description' => "Quote PDF emailed to {$toEmail}."
            ]);
            
            $quote->status = 'quote_sent';
            $quote->save();

            return redirect()->back()->with('success', "Quote successfully emailed to {$toEmail}!");
        }

        return redirect()->back()->with('error', 'Failed to send email. Please check your MS Graph connection settings.');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Quote deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:quotes,id',
        ]);

        Quote::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', count($request->ids) . ' quotes deleted successfully.');
    }

    public function reply(Request $request, Quote $quote)
    {
        $request->validate([
            'message' => 'required|string',
            'thread_type' => 'required|in:customer,vendor',
            'to_email' => 'required|email',
            'cc_emails' => 'nullable|string'
        ]);

        $ccEmails = array_filter(array_map('trim', explode(',', $request->input('cc_emails', ''))));

        $subject = 'Re: Your Quote is Ready: ' . $quote->project_name;
        if ($request->thread_type === 'vendor') {
            $subject = 'Re: Pricing Request - ' . $quote->project_name . ' (Quote #' . $quote->quote_number . ')';
        }

        $htmlContent = nl2br(e($request->message));
        
        $outlookService = new \App\Services\OutlookService();
        $success = $outlookService->sendEmail($request->to_email, $subject, $htmlContent, [], $ccEmails);

        if ($success) {
            $quote->emails()->create([
                'thread_type' => $request->thread_type,
                'direction'   => 'outbound',
                'from_email'  => env('SHARED_MAILBOX_ADDRESS', 'sales@electricsupplyconnections.com'),
                'to_email'    => $request->to_email,
                'cc_emails'   => implode(', ', $ccEmails),
                'subject'     => $subject,
                'body_html'   => $htmlContent,
                'body_text'   => $request->message,
                'sent_at'     => now(),
            ]);

            return redirect()->back()->with('success', 'Reply sent successfully!');
        }

        return redirect()->back()->with('error', 'Failed to send reply via Microsoft Graph.');
    }
}
