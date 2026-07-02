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
