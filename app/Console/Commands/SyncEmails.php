<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OutlookService;
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

                // We assume inbound since we are reading inbox
                // We don't have quote_id yet, so we leave it null 
                // We set thread_type to 'customer' as default, can be re-assigned later
                
                $body = $msg['body'] ?? null;
                $content = $body ? ($body['content'] ?? '') : '';

                Email::create([
                    'graph_message_id' => $msg['id'],
                    'conversation_id' => $msg['conversationId'] ?? null,
                    'thread_type' => 'customer', 
                    'direction' => 'inbound',
                    'from_name' => $senderName,
                    'from_email' => $senderEmail,
                    'to_email' => config('services.msgraph.shared_mailbox'),
                    'subject' => $msg['subject'] ?? '(No Subject)',
                    'body_html' => $content,
                    'body_text' => strip_tags($content),
                    'has_attachments' => $msg['hasAttachments'] ?? false,
                    'sent_at' => Carbon::parse($msg['sentDateTime']),
                ]);

                $count++;
            }

            $this->info("Successfully synced {$count} new emails.");
            
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Email sync failed', ['error' => $e]);
        }
    }
}
