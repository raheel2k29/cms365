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
}
