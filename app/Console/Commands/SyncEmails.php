<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OutlookService;
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Quote;
use App\Models\BusinessEntity;
use App\Models\QuoteType;
use App\Models\Attachment;

class SyncEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync emails from Microsoft Graph API shared mailbox';

    /**
     * Execute the console command.
     */
    public function handle(OutlookService $outlookService)
    {
        $this->info('Starting email sync...');

        try {
            $messages = $outlookService->getInboxMessages(50);
            
            if (empty($messages)) {
                $this->info('No messages fetched or error occurred.');
                return;
            }

            $this->info('Successfully connected to MS Graph! Found ' . count($messages) . ' messages in the inbox.');

            $count = 0;

            foreach ($messages as $msg) {
                $existing = Email::where('graph_message_id', $msg['id'])->first();
                if ($existing) {
                    continue;
                }

                $from = $msg['from'] ?? null;
                $senderEmail = $from ? ($from['emailAddress']['address'] ?? 'unknown@example.com') : 'unknown@example.com';
                $senderName = $from ? ($from['emailAddress']['name'] ?? 'Unknown') : 'Unknown';

                // 1. Match or Create Contact & Company
                $domain = Str::after($senderEmail, '@');
                $companyName = explode('@', $senderEmail)[0] . ' Company';
                if ($domain !== 'gmail.com' && $domain !== 'yahoo.com' && $domain !== 'hotmail.com' && $domain !== 'example.com') {
                    $companyName = ucfirst(explode('.', $domain)[0]);
                }

                $contact = Contact::where('email', $senderEmail)->first();
                if (!$contact) {
                    $company = Company::firstOrCreate(
                        ['name' => $companyName],
                        ['notes' => 'Auto-created from email sync']
                    );

                    $contact = Contact::create([
                        'company_id' => $company->id,
                        'name' => $senderName,
                        'email' => $senderEmail,
                        'is_primary' => true,
                    ]);
                }

                // 2. Thread Matching
                $conversationId = $msg['conversationId'] ?? null;
                $subject = $msg['subject'] ?? '(No Subject)';
                $quoteId = null;
                
                if ($conversationId) {
                    $existingThreadEmail = Email::where('conversation_id', $conversationId)
                                              ->whereNotNull('quote_id')
                                              ->first();
                    if ($existingThreadEmail) {
                        $quoteId = $existingThreadEmail->quote_id;
                    }
                }

                // Fallback: Smart Subject Matching (if outbound email didn't have a conversation_id)
                if (!$quoteId && $subject) {
                    $cleanSubject = trim(preg_replace('/^(Re|Fwd|Fw|RE|FWD|FW):\s*/i', '', $subject));
                    $outboundMatch = Email::where('subject', $cleanSubject)->whereNotNull('quote_id')->first();
                    if ($outboundMatch) {
                        $quoteId = $outboundMatch->quote_id;
                        // Since we found a match, let's also update the outbound email's conversation_id to fix the thread!
                        if ($conversationId && !$outboundMatch->conversation_id) {
                            $outboundMatch->conversation_id = $conversationId;
                            $outboundMatch->save();
                        }
                    }
                }

                $body = $msg['body'] ?? null;
                $content = $body ? ($body['content'] ?? '') : '';

                // 3. Create Email Record
                $email = Email::create([
                    'quote_id' => $quoteId, // Null if new thread
                    'graph_message_id' => $msg['id'],
                    'conversation_id' => $conversationId,
                    'thread_type' => 'customer', 
                    'direction' => 'inbound',
                    'from_name' => $senderName,
                    'from_email' => $senderEmail,
                    'to_email' => config('services.msgraph.shared_mailbox'),
                    'subject' => $subject,
                    'body_html' => $content,
                    'body_text' => strip_tags($content),
                    'has_attachments' => $msg['hasAttachments'] ?? false,
                    'sent_at' => Carbon::parse($msg['sentDateTime']),
                ]);

                // 4. Download and Save Attachments
                if ($msg['hasAttachments'] ?? false) {
                    $attachments = $outlookService->getMessageAttachments($msg['id']);
                    foreach ($attachments as $att) {
                        if (isset($att['@odata.type']) && $att['@odata.type'] === '#microsoft.graph.fileAttachment') {
                            $fileName = $att['name'];
                            $contentBytes = $att['contentBytes'] ?? '';
                            $size = $att['size'] ?? 0;
                            $contentType = $att['contentType'] ?? 'application/octet-stream';
                            
                            if ($contentBytes) {
                                $decodedContent = base64_decode($contentBytes);
                                $storedName = Str::uuid() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $fileName);
                                $filePath = 'attachments/' . $storedName;
                                
                                Storage::disk('public')->put($filePath, $decodedContent);
                                
                                Attachment::create([
                                    'quote_id' => $quoteId, // Can be null
                                    'email_id' => $email->id,
                                    'original_name' => $fileName,
                                    'stored_name' => $storedName,
                                    'file_path' => $filePath,
                                    'mime_type' => $contentType,
                                    'file_size' => $size,
                                    'source' => 'email'
                                ]);
                            }
                        }
                    }
                }

                $count++;
            }

            $this->info("Successfully synced {$count} new emails.");
            
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Email sync failed', ['error' => $e]);
        }
    }
}
