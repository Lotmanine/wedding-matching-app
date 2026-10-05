<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\HealthSnapshotController;
use App\Http\Controllers\Api\V1\Admin\N8nHeartbeatController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\N8n\ReceiptStreamController;
use App\Http\Controllers\Api\V1\N8n\WhatsappConfigController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ServiceRequestController;
use App\Http\Controllers\Api\V1\WedRequestController;
use App\Http\Controllers\Api\V1\Webhooks\WhatsappDeliveryStatusController;
use App\Http\Controllers\Api\V1\Whatsapp\InboundButtonTapController;
use App\Http\Controllers\Api\V1\Whatsapp\InboundStatusUpdateController;
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

        Route::middleware('throttle:120,1')->group(function () {
            Route::post('/webhooks/whatsapp/delivery-status', WhatsappDeliveryStatusController::class)
                ->name('api.v1.webhooks.whatsapp.delivery_status');
        });

        Route::middleware('throttle:240,1')->group(function () {
            Route::post('/whatsapp/inbound/button-tap', InboundButtonTapController::class)
                ->name('api.v1.whatsapp.inbound.button_tap');
        });

        Route::middleware('throttle:600,1')->group(function () {
            Route::post('/whatsapp/inbound/status-update', InboundStatusUpdateController::class)
                ->name('api.v1.whatsapp.inbound.status_update');
        });

        Route::middleware('throttle:4,1')->group(function () {
            Route::get('/admin/ops/health-snapshot', HealthSnapshotController::class)
                ->name('api.v1.admin.ops.health_snapshot');
            Route::post('/webhooks/n8n/heartbeat', N8nHeartbeatController::class)
                ->name('api.v1.webhooks.n8n.heartbeat');
        });

        Route::middleware('throttle:60,1')->group(function () {
            Route::get('/n8n/receipts/{purchaseUid}.pdf', [ReceiptStreamController::class, 'show'])
                ->name('api.v1.n8n.receipts.show');
            Route::patch('/n8n/receipts/{purchaseUid}/mark-emailed', [ReceiptStreamController::class, 'markEmailed'])
                ->name('api.v1.n8n.receipts.mark_emailed');
        });

        // Meta WhatsApp credentials configured in /admin/whatsapp, fetched by
        // the n8n zawaj-whatsapp workflow on every outbound + inbound run.
        Route::middleware('throttle:600,1')->group(function () {
            Route::get('/n8n/whatsapp-config', WhatsappConfigController::class)
                ->name('api.v1.n8n.whatsapp_config');
        });
    });
});

Route::post('/webhooks/n8n/error', [N8nWebhookController::class, 'error'])
    ->middleware('throttle:60,1')
    ->name('api.webhooks.n8n.error');
