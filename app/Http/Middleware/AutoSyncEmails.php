<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoSyncEmails
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if 60 seconds have passed since last sync
        if (!\Illuminate\Support\Facades\Cache::has('email_sync_lock')) {
            // Set the lock for 60 seconds
            \Illuminate\Support\Facades\Cache::put('email_sync_lock', true, 60);

            // Run the sync command AFTER the HTTP response is sent to the user
            app()->terminating(function () {
                try {
                    \Illuminate\Support\Facades\Artisan::call('emails:sync');
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('AutoSync failed: ' . $e->getMessage());
                }
            });
        }

        return $response;
    }
}
