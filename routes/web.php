<?php

declare(strict_types=1);

use App\Http\Controllers\Couple\CoupleRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Provider\ProviderDashboardController;
use App\Http\Controllers\Provider\ProviderLeadsController;
use App\Http\Controllers\Provider\ProviderLoginController;
use App\Http\Controllers\Provider\ProviderPasswordResetController;
use App\Http\Controllers\Provider\ProviderProfileController;
use App\Http\Controllers\Provider\ProviderRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/demande', [CoupleRequestController::class, 'create'])
    ->name('couple.request.create');

Route::get('/demande/confirmation/{wedUid}', [CoupleRequestController::class, 'confirmation'])
    ->name('couple.request.confirmation');

Route::prefix('provider')->name('provider.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/register', [ProviderRegistrationController::class, 'create'])->name('register');

        Route::get('/login', [ProviderLoginController::class, 'create'])->name('login');
        Route::post('/login', [ProviderLoginController::class, 'store'])->name('login.store');

        Route::get('/forgot-password', [ProviderPasswordResetController::class, 'requestForm'])->name('password.request');
        Route::post('/forgot-password', [ProviderPasswordResetController::class, 'sendLink'])->name('password.email');

        Route::get('/reset-password/{token}', [ProviderPasswordResetController::class, 'resetForm'])->name('password.reset');
        Route::post('/reset-password', [ProviderPasswordResetController::class, 'update'])->name('password.update');
    });

    Route::middleware(['auth', 'role:provider'])->group(function () {
        Route::post('/logout', [ProviderLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProviderProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/leads', [ProviderLeadsController::class, 'index'])->name('leads.index');
        Route::get('/leads/{serviceUid}', [ProviderLeadsController::class, 'show'])->name('leads.show');

        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/leads/{serviceUid}/accept', [ProviderLeadsController::class, 'accept'])->name('leads.accept');
            Route::post('/leads/{serviceUid}/refuse', [ProviderLeadsController::class, 'refuse'])->name('leads.refuse');
        });
    });
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
