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

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quotes
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
    Route::resource('quotes', QuoteController::class);
    Route::post('quotes/{quote}/notes', [\App\Http\Controllers\NoteController::class, 'store'])->name('quotes.notes.store');
    Route::post('quotes/{quote}/attachments', [\App\Http\Controllers\AttachmentController::class, 'store'])->name('quotes.attachments.store');
    Route::get('attachments/{attachment}/download', [\App\Http\Controllers\AttachmentController::class, 'download'])->name('attachments.download');

    // Contacts
    Route::resource('contacts', ContactController::class);

    // Companies (customers)
    Route::resource('customers', CustomerController::class);

    // Vendors
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


Route::get('/setup-db', function () {
    try {
        Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
        // Run specific seeders or default seeder if they exist
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return 'Database migrated and seeded successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});


Route::get('/debug-env', function () {
    return response()->json([
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'POSTGRES_URL' => env('POSTGRES_URL') ? 'set' : 'not set',
        'DATABASE_URL' => env('DATABASE_URL') ? 'set' : 'not set',
        'PGHOST' => env('PGHOST') ? 'set' : 'not set',
        'NEON' => array_filter($_ENV, function($k) { return str_contains($k, 'PG') || str_contains($k, 'DB') || str_contains($k, 'DATABASE'); }, ARRAY_FILTER_USE_KEY)
    ]);
});
