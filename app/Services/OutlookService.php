<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutlookService
{
    private $tenantId;
    private $clientId;
    private $clientSecret;
    private $sharedMailbox;

    public function __construct()
    {
        $this->tenantId = config('services.msgraph.tenant_id');
        $this->clientId = config('services.msgraph.client_id');
        $this->clientSecret = config('services.msgraph.client_secret');
        $this->sharedMailbox = config('services.msgraph.shared_mailbox');
    }

    /**
     * Get an App-Only Access Token from Microsoft Entra ID.
     */
    public function getAccessToken()
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        $response = Http::withoutVerifying()->asForm()->post($url, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('Failed to get MS Graph access token', [
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return null;
    }

    /**
     * Fetch emails from the shared mailbox.
     * @param int $limit
     * @return array
     */
    public function getInboxMessages($limit = 50)
    {
        $token = $this->getAccessToken();
        if (!$token) return [];

        $url = "https://graph.microsoft.com/v1.0/users/{$this->sharedMailbox}/messages?\$top={$limit}";
        
        $response = Http::withoutVerifying()->withToken($token)->get($url);

        if ($response->successful()) {
            return $response->json('value') ?? [];
        }

        Log::error('Failed to fetch inbox messages', ['error' => $response->body()]);
        return [];
    }

    /**
     * Fetch attachments for a specific message from MS Graph.
     * @param string $messageId
     * @return array
     */
    public function getMessageAttachments(string $messageId)
    {
        $token = $this->getAccessToken();
        if (!$token) return [];

        $url = "https://graph.microsoft.com/v1.0/users/{$this->sharedMailbox}/messages/{$messageId}/attachments";
        
        $response = Http::withoutVerifying()->withToken($token)->get($url);

        if ($response->successful()) {
            return $response->json('value') ?? [];
        }

        Log::error("Failed to fetch attachments for message {$messageId}", ['error' => $response->body()]);
        return [];
    }

    /**
     * Send an email from the shared mailbox using MS Graph.
     * 
     * @param string $toEmail
     * @param string $subject
     * @param string $contentHtml
     * @param array $attachments Array of absolute file paths to attach
     * @return bool
     */
    public function sendEmail(string $toEmail, string $subject, string $contentHtml, array $attachments = [])
    {
        $token = $this->getAccessToken();
        if (!$token) return false;

        $url = "https://graph.microsoft.com/v1.0/users/{$this->sharedMailbox}/sendMail";

        // Prepare the payload
        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $contentHtml,
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $toEmail,
                        ]
                    ]
                ],
                'hasAttachments' => count($attachments) > 0,
            ],
            'saveToSentItems' => 'true'
        ];

        // Process attachments if any exist
        if (!empty($attachments)) {
            $graphAttachments = [];
            foreach ($attachments as $filePath) {
                if (file_exists($filePath)) {
                    $fileName = basename($filePath);
                    $fileContent = file_get_contents($filePath);
                    $graphAttachments[] = [
                        '@odata.type' => '#microsoft.graph.fileAttachment',
                        'name' => $fileName,
                        'contentType' => mime_content_type($filePath) ?: 'application/octet-stream',
                        'contentBytes' => base64_encode($fileContent)
                    ];
                }
            }
            if (!empty($graphAttachments)) {
                $payload['message']['attachments'] = $graphAttachments;
            }
        }

        $response = Http::withoutVerifying()->withToken($token)->post($url, $payload);

        if ($response->successful()) {
            Log::info("Successfully sent email via MS Graph to {$toEmail}");
            return true;
        }

        Log::error("Failed to send email via MS Graph to {$toEmail}", [
            'status' => $response->status(),
            'error' => $response->body()
        ]);
        
        return false;
    }
}
