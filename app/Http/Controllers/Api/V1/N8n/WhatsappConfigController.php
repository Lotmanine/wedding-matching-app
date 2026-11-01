<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\N8n;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Whatsapp\WhatsappSettings;
use Illuminate\Http\JsonResponse;

/**
 * Serves the admin-configured Meta WhatsApp credentials to the n8n
 * `wedding-whatsapp` workflow (Sanctum, ability `n8n-service`). This is the
 * only place the decrypted secrets leave Laravel.
 */
class WhatsappConfigController extends Controller
{
    public function __invoke(WhatsappSettings $whatsapp): JsonResponse
    {
        return ApiResponse::success($whatsapp->forN8n())
            ->header('Cache-Control', 'no-store');
    }
}
