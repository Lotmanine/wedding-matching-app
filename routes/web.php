<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminLogsController;
use App\Http\Controllers\Admin\AdminPacksController;
use App\Http\Controllers\Admin\AdminProvidersController;
use App\Http\Controllers\Admin\AdminPurchasesController;
use App\Http\Controllers\Admin\AdminRequestsController;
use App\Http\Controllers\Admin\AdminServiceRequestsController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminWebhooksController;
use App\Http\Controllers\Couple\CoupleRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Provider\CreditsController;
use App\Http\Controllers\Provider\ProviderDashboardController;
use App\Http\Controllers\Provider\ProviderLeadsController;
use App\Http\Controllers\Provider\ProviderLoginController;
use App\Http\Controllers\Provider\ProviderPasswordResetController;
use App\Http\Controllers\Provider\ProviderProfileController;
use App\Http\Controllers\Provider\ProviderRegistrationController;
use App\Http\Controllers\Webhooks\ChargilyWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/demande', [CoupleRequestController::class, 'create'])
    ->name('couple.request.create');

Route::get('/demande/confirmation/{wedUid}', [CoupleRequestController::class, 'confirmation'])
    ->name('couple.request.confirmation');

// Legal / ANPDP pages (public)
Route::view('/confidentialite', 'legal.privacy')->name('legal.privacy');
Route::view('/conditions-utilisation', 'legal.terms')->name('legal.terms');

Route::prefix('provider')->name('provider.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('/register', [ProviderRegistrationController::class, 'create'])->name('register');

        Route::get('/login', [ProviderLoginController::class, 'create'])->name('login');
        Route::post('/login', [ProviderLoginController::class, 'store'])
            ->middleware('throttle:login')
            ->name('login.store');

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

        Route::get('/credits', [CreditsController::class, 'index'])->name('credits.index');
        Route::get('/credits/success', [CreditsController::class, 'success'])->name('credits.success');
        Route::get('/credits/failure', [CreditsController::class, 'failure'])->name('credits.failure');
        Route::get('/credits/history', [CreditsController::class, 'history'])->name('credits.history');
        Route::get('/credits/purchases/{purchaseUid}', [CreditsController::class, 'showPurchase'])->name('credits.purchase.show');

        Route::post('/credits/checkout/{packSlug}', [CreditsController::class, 'checkout'])
            ->middleware('throttle:5,1')
            ->name('credits.checkout');
    });
});

Route::post('/webhooks/chargily', ChargilyWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.chargily');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])
            ->middleware('throttle:login')
            ->name('login.store');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/providers', [AdminProvidersController::class, 'index'])->name('providers.index');
        Route::get('/providers/{id}', [AdminProvidersController::class, 'show'])->name('providers.show');
        Route::post('/providers/{id}/verify', [AdminProvidersController::class, 'verify'])->name('providers.verify');
        Route::post('/providers/{id}/unverify', [AdminProvidersController::class, 'unverify'])->name('providers.unverify');
        Route::post('/providers/{id}/suspend', [AdminProvidersController::class, 'suspend'])->name('providers.suspend');
        Route::post('/providers/{id}/reactivate', [AdminProvidersController::class, 'reactivate'])->name('providers.reactivate');
        Route::post('/providers/{id}/credits/adjust', [AdminProvidersController::class, 'adjustCredits'])
            ->middleware('throttle:20,60')
            ->name('providers.credits.adjust');

        Route::get('/requests', [AdminRequestsController::class, 'index'])->name('requests.index');
        Route::get('/requests/{uid}', [AdminRequestsController::class, 'show'])->name('requests.show');
        Route::post('/requests/{uid}/expire', [AdminRequestsController::class, 'expire'])->name('requests.expire');
        Route::post('/requests/{uid}/delete', [AdminRequestsController::class, 'delete'])
            ->middleware('throttle:5,60')
            ->name('requests.delete');

        Route::get('/service-requests', [AdminServiceRequestsController::class, 'index'])->name('service_requests.index');
        Route::get('/service-requests/{uid}', [AdminServiceRequestsController::class, 'show'])->name('service_requests.show');
        Route::post('/service-requests/{uid}/force-open', [AdminServiceRequestsController::class, 'forceOpen'])->name('service_requests.force_open');

        Route::get('/packs', [AdminPacksController::class, 'index'])->name('packs.index');
        Route::get('/packs/create', [AdminPacksController::class, 'create'])->name('packs.create');
        Route::post('/packs', [AdminPacksController::class, 'store'])->name('packs.store');
        Route::get('/packs/{id}/edit', [AdminPacksController::class, 'edit'])->name('packs.edit');
        Route::put('/packs/{id}', [AdminPacksController::class, 'update'])->name('packs.update');
        Route::post('/packs/{id}/toggle', [AdminPacksController::class, 'toggle'])->name('packs.toggle');

        Route::get('/purchases', [AdminPurchasesController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{uid}', [AdminPurchasesController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{uid}/mark-paid', [AdminPurchasesController::class, 'markPaid'])->name('purchases.mark_paid');

        Route::get('/users', [AdminUsersController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [AdminUsersController::class, 'show'])->name('users.show');

        Route::get('/logs/audit', [AdminLogsController::class, 'audit'])->name('logs.audit');

        Route::prefix('webhooks')->name('webhooks.')->group(function () {
            Route::get('/', [AdminWebhooksController::class, 'index'])->name('index');
            Route::get('/create', [AdminWebhooksController::class, 'create'])->name('create');
            Route::post('/', [AdminWebhooksController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AdminWebhooksController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminWebhooksController::class, 'update'])->name('update');
            Route::post('/{id}/toggle', [AdminWebhooksController::class, 'toggle'])->name('toggle');
            Route::delete('/{id}', [AdminWebhooksController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/deliveries', [AdminWebhooksController::class, 'deliveries'])->name('deliveries');
            Route::post('/deliveries/{id}/replay', [AdminWebhooksController::class, 'replay'])->name('deliveries.replay');
        });

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
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

require __DIR__.'/auth.php';
