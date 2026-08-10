<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/rooms', function () {
        return view('rooms.index');
    })->name('rooms.index');

    Route::get('/tenants', function(){
        return view('tenants.index');
    })->name('tenants.index');

    Route::get('/leases', function () {
        return view('leases.index');
    })->name('leases.index');

    Route::get('/payments', function () {
        return view('payments.index');
    })->name('payments.index');

    Route::get('/maintenance', function () {
        return view('maintenance.index');
    })->name('maintenance.index');

    Route::get('/expenses', function () {
        return view('expenses.index');
    })->name('expenses.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    Route::get('/facilities', function () {
        return view('facilities.index');
    })->name('facilities.index');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
});



require __DIR__.'/auth.php';
