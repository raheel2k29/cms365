<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

// Vercel Serverless Sync Trigger
Route::get('/api/sync-emails', function () {
    \Illuminate\Support\Facades\Artisan::call('emails:sync');
    return response()->json([
        'status' => 'success',
        'output' => \Illuminate\Support\Facades\Artisan::output()
    ]);
});

// Temporary Route to test sending an email
Route::get('/api/test-send-email', function (\Illuminate\Http\Request $request) {
    $to = $request->query('to');
    if (!$to) {
        return "Please provide an email address like this: /api/test-send-email?to=your_email@example.com";
    }

    $outlookService = new \App\Services\OutlookService();
    $subject = "Test Email from Quote CRM";
    $htmlContent = "<h1>It works!</h1><p>This email was successfully sent using the Microsoft Graph API from your shared mailbox!</p>";
    
    $success = $outlookService->sendEmail($to, $subject, $htmlContent);
    
    if ($success) {
        return "SUCCESS! Check the inbox of {$to} for the test email.";
    } else {
        return "FAILED! Check your Laravel logs for the exact MS Graph API error.";
    }
});

Route::get('/api/debug-attachments', function () {
    $outlookService = new \App\Services\OutlookService();
    $messages = $outlookService->getInboxMessages(5);
    
    $results = [];
    foreach ($messages as $msg) {
        if ($msg['hasAttachments']) {
            $attachments = $outlookService->getMessageAttachments($msg['id']);
            
            // Strip huge contentBytes from output so it doesn't crash the browser
            foreach ($attachments as &$att) {
                if (isset($att['contentBytes'])) {
                    $att['contentBytes'] = 'REMOVED_FOR_DEBUG_OUTPUT (length: ' . strlen($att['contentBytes']) . ')';
                }
            }
            
            $results[] = [
                'subject' => $msg['subject'],
                'message_id' => $msg['id'],
                'attachments' => $attachments
            ];
        }
    }
    return response()->json($results);
});

Route::get('/api/migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Database migrated successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pipeline
    Route::get('/pipeline', [\App\Http\Controllers\PipelineController::class, 'index'])->name('pipeline.index');
    Route::post('/pipeline/quick-update', [\App\Http\Controllers\PipelineController::class, 'quickUpdate'])->name('pipeline.quick-update');

    // Catalog & Pricing
    Route::get('/catalog', [\App\Http\Controllers\CatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog/import', [\App\Http\Controllers\CatalogController::class, 'import'])->name('catalog.import');
    Route::get('/api/catalog/search', [\App\Http\Controllers\CatalogController::class, 'search'])->name('catalog.search');

    // Quotes
    Route::post('quotes/bulk-delete', [QuoteController::class, 'bulkDelete'])->name('quotes.bulk-delete');
    Route::get('quotes/bulk-delete', fn() => redirect()->route('quotes.index'));
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::post('quotes/{quote}/quick-update', [QuoteController::class, 'quickUpdate'])->name('quotes.quick-update');
    Route::post('quotes/{quote}/send-email', [QuoteController::class, 'sendEmail'])->name('quotes.send-email');
    Route::post('quotes/{quote}/reply', [QuoteController::class, 'reply'])->name('quotes.reply');
    Route::get('quotes/calendar', [QuoteController::class, 'calendar'])->name('quotes.calendar');
    Route::resource('quotes', QuoteController::class);
    Route::post('quotes/{quote}/notes', [\App\Http\Controllers\NoteController::class, 'store'])->name('quotes.notes.store');
    Route::post('quotes/{quote}/attachments', [\App\Http\Controllers\AttachmentController::class, 'store'])->name('quotes.attachments.store');
    Route::get('attachments/{attachment}/download', [\App\Http\Controllers\AttachmentController::class, 'download'])->name('attachments.download');
    
    // Vendor Pricing Requests
    Route::get('quotes/{quote}/vendor-requests/create', [\App\Http\Controllers\QuoteVendorRequestController::class, 'create'])->name('quotes.vendor-requests.create');
    Route::post('quotes/{quote}/vendor-requests', [\App\Http\Controllers\QuoteVendorRequestController::class, 'store'])->name('quotes.vendor-requests.store');

    // Contacts
    Route::post('contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
    Route::get('contacts/bulk-delete', fn() => redirect()->route('contacts.index'));
    Route::resource('contacts', ContactController::class);

    // Companies (customers)
    Route::post('customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
    Route::get('customers/bulk-delete', fn() => redirect()->route('customers.index'));
    Route::resource('customers', CustomerController::class);

    // Vendors
    Route::post('vendors/bulk-delete', [VendorController::class, 'bulkDelete'])->name('vendors.bulk-delete');
    Route::get('vendors/bulk-delete', fn() => redirect()->route('vendors.index'));
    Route::resource('vendors', VendorController::class);

    // Emails (Microsoft 365 inbox — Phase 5)
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::get('/emails/{email}', [EmailController::class, 'show'])->name('emails.show');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Settings (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


