<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ServiceRequestController;
use App\Http\Controllers\Api\V1\WedRequestController;
use App\Http\Controllers\Webhooks\N8nWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'index'])->name('api.v1.health');

    Route::middleware(['auth:sanctum', 'ability:n8n-service', 'throttle:api'])->group(function () {
        Route::get('/wed-requests/{wedUid}', [WedRequestController::class, 'show']);

        Route::get('/service-requests', [ServiceRequestController::class, 'index']);
        Route::get('/service-requests/pending-matching', [ServiceRequestController::class, 'pendingMatching']);
        Route::get('/service-requests/{uid}', [ServiceRequestController::class, 'show']);

        Route::middleware('throttle:api-mutations')->group(function () {
            Route::post('/service-requests/{uid}/start-round', [ServiceRequestController::class, 'startRound']);
            Route::post('/service-requests/{uid}/accept', [ServiceRequestController::class, 'accept']);
            Route::post('/service-requests/{uid}/refuse', [ServiceRequestController::class, 'refuse']);
            Route::post('/service-requests/{uid}/force-open', [ServiceRequestController::class, 'forceOpen']);
        });

        Route::get('/providers/eligible', [ProviderController::class, 'eligible']);
        Route::get('/providers/ranking-candidates', [ProviderController::class, 'rankingCandidates']);
        Route::get('/providers/{id}', [ProviderController::class, 'show']);
    });
});

Route::post('/webhooks/n8n/error', [N8nWebhookController::class, 'error'])
    ->middleware('throttle:60,1')
    ->name('api.webhooks.n8n.error');
