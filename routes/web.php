<?php

declare(strict_types=1);

use App\Http\Controllers\Couple\CoupleRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/demande', [CoupleRequestController::class, 'create'])
    ->name('couple.request.create');

Route::get('/demande/confirmation/{wedUid}', [CoupleRequestController::class, 'confirmation'])
    ->name('couple.request.confirmation');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
