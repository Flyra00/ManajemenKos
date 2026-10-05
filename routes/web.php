<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PublicRoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\InvoiceController;

Route::get('/', [PublicRoomController::class, 'landing'])->name('home');

// Katalog Publik & Booking Kamar
Route::get('/kamar', [PublicRoomController::class, 'index'])->name('public.rooms.index');
Route::get('/kamar/{room}', [PublicRoomController::class, 'show'])->name('public.rooms.show');
Route::post('/kamar/{room}/sewa', [BookingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.rooms.book');

// Invoice Publik & Upload Bukti Pembayaran
//
// Rute invoice TIDAK boleh memakai nomor invoice mentah sebagai kunci akses,
// karena nomor invoice bisa dienumerasi dan berisi data pribadi penyewa.
// Karena itu seluruh rute di bawah wajib memakai SIGNED URL (link bertanda tangan
// yang dikirim pengelola ke penyewa), bukan route() biasa.
// Gunakan URL::signedRoute() atau $payment->public_url.
Route::middleware('signed')->group(function () {
    Route::get('/invoices/{invoice_number}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice_number}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::get('/invoices/{invoice_number}/status', [InvoiceController::class, 'status'])->name('invoices.status');
    Route::get('/invoices/{invoice_number}/snap-token', [InvoiceController::class, 'getSnapToken'])->name('invoices.snap-token');

    Route::post('/invoices/{invoice_number}/pay', [InvoiceController::class, 'uploadProof'])
        ->middleware('throttle:10,1')
        ->name('invoices.pay');
});

// Webhook Notifikasi Pembayaran Midtrans (Dipanggil otomatis oleh server Midtrans)
Route::post('/midtrans/notification', [InvoiceController::class, 'midtransNotification'])
    ->name('midtrans.notification');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dokumen Cetak Resmi (Kuitansi & Kontrak Sewa)
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/leases/{lease}/contract', [LeaseController::class, 'contract'])->name('leases.contract');
    Route::get('/leases/{lease}/checkout-receipt', [LeaseController::class, 'checkoutReceipt'])->name('leases.checkout-receipt');
    Route::post('/leases/{lease}/request-bill', [LeaseController::class, 'requestBill'])->name('tenant.leases.request-bill');

    // Modul Maintenance: Akses CRUD (scoping otorisasi dikendalikan di MaintenanceController)
    Route::resource('maintenance', MaintenanceController::class);
    Route::put('/maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])
        ->middleware('role:admin|staff')
        ->name('maintenance.update-status');

    // Rute Mutasi Operasional & Keuangan (Khusus Admin & Staff)
    Route::middleware('role:admin|staff')->group(function () {
        Route::post('/payments/generate-bills', [PaymentController::class, 'generateBills'])->name('payments.generate_bills');
        Route::resource('payments', PaymentController::class)->except(['index', 'show']);
        Route::put('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::resource('expenses', ExpenseController::class)->except(['index', 'show']);
    });

    // Rute Mutasi Data Master & Pengaturan (Khusus Admin)
    Route::middleware('role:admin')->group(function () {
        Route::resource('rooms', RoomController::class)->except(['index', 'show']);
        Route::resource('facilities', FacilityController::class)->except(['index', 'show']);
        Route::resource('tenants', TenantController::class)->except(['index', 'show']);
        Route::post('/leases/{lease}/renew', [LeaseController::class, 'renew'])->name('leases.renew');
        Route::post('/leases/{lease}/checkout', [LeaseController::class, 'checkout'])->name('leases.checkout');
        Route::resource('leases', LeaseController::class)->except(['index', 'show']);

        Route::put('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
        Route::put('/settings/kos', [SettingController::class, 'updateKosInfo'])->name('settings.kos');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('/users/{user}/verify-email', [UserController::class, 'verifyEmail'])->name('users.verify-email');
        Route::put('/settings/users/{user}/role', [UserController::class, 'updateRole'])->name('settings.users.role');
    });

    // Rute Monitoring Keuangan & Operasional (Dapat diakses oleh Admin, Owner, dan Staff)
    Route::middleware('role:admin|owner|staff')->group(function () {
        Route::resource('payments', PaymentController::class)->only(['index', 'show']);
        Route::resource('expenses', ExpenseController::class)->only(['index', 'show']);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Rute Monitoring Properti & Pengaturan (Khusus Admin & Owner)
    Route::middleware('role:admin|owner')->group(function () {
        Route::resource('rooms', RoomController::class)->only(['index', 'show']);
        Route::resource('facilities', FacilityController::class)->only(['index', 'show']);
        Route::resource('tenants', TenantController::class)->only(['index', 'show']);
        Route::resource('leases', LeaseController::class)->only(['index', 'show']);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    });
});







require __DIR__.'/auth.php';
