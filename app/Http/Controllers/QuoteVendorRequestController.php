<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuoteVendorRequestController extends Controller
{
    public function create(\App\Models\Quote $quote)
    {
        $quote->load('items', 'company');
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        return view('quotes.vendor_requests.create', compact('quote', 'vendors'));
    }

    public function store(\Illuminate\Http\Request $request, \App\Models\Quote $quote)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:quote_items,id',
            'message' => 'required|string',
        ]);

        $vendor = \App\Models\Vendor::findOrFail($request->vendor_id);
        
        // Ensure vendor has email
        if (!$vendor->email) {
            return redirect()->back()->with('error', 'The selected vendor does not have an email address.');
        }

        // We could generate a PDF or just put the items in the HTML.
        // Let's create an HTML table of items.
        $items = \App\Models\QuoteItem::whereIn('id', $request->item_ids)->get();
        
        $itemsHtml = "<table style='width:100%; border-collapse:collapse; margin:20px 0;'>
                        <thead>
                            <tr style='background:#f3f4f6'>
                                <th style='padding:10px; border:1px solid #e5e7eb; text-align:left'>Description</th>
                                <th style='padding:10px; border:1px solid #e5e7eb; text-align:center'>Qty</th>
                            </tr>
                        </thead>
                        <tbody>";
        foreach ($items as $item) {
            $itemsHtml .= "<tr>
                            <td style='padding:10px; border:1px solid #e5e7eb'>{$item->description}</td>
                            <td style='padding:10px; border:1px solid #e5e7eb; text-align:center'>{$item->qty}</td>
                           </tr>";
        }
        $itemsHtml .= "</tbody></table>";

        $htmlContent = nl2br(e($request->message)) . $itemsHtml;
        $subject = "Pricing Request - {$quote->project_name} (Quote #{$quote->quote_number})";

        $outlookService = new \App\Services\OutlookService();
        $success = $outlookService->sendEmail($vendor->email, $subject, $htmlContent);

        if ($success) {
            // Save vendor request record
            \Illuminate\Support\Facades\DB::table('quote_vendor_requests')->insert([
                'quote_id' => $quote->id,
                'vendor_id' => $vendor->id,
                'status' => 'pending',
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Save email to vendor thread
            $quote->emails()->create([
                'thread_type' => 'vendor',
                'direction'   => 'outbound',
                'from_email'  => env('SHARED_MAILBOX_ADDRESS', 'sales@electricsupplyconnections.com'),
                'to_email'    => $vendor->email,
                'subject'     => $subject,
                'body_html'   => $htmlContent,
                'body_text'   => strip_tags(str_replace('<br>', "\n", $htmlContent)),
                'sent_at'     => now(),
            ]);

            // Update items to tag vendor
            \App\Models\QuoteItem::whereIn('id', $request->item_ids)->update(['vendor_id' => $vendor->id]);

            return redirect()->route('quotes.show', $quote)->with('success', "Pricing request sent to {$vendor->name}!");
        }

        return redirect()->back()->with('error', 'Failed to send email. Please check your MS Graph connection settings.');
    }
}
