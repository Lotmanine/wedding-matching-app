<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $db = 'unknown';
        try {
            DB::connection()->getPdo();
            $db = 'connected';
        } catch (\Throwable $e) {
            $db = 'down';
        }

        $cache = 'unknown';
        try {
            Cache::put('__health_probe', 1, 5);
            $cache = Cache::get('__health_probe') === 1 ? 'connected' : 'down';
        } catch (\Throwable $e) {
            $cache = 'down';
        }

        return ApiResponse::success([
            'status'      => $db === 'connected' ? 'ok' : 'degraded',
            'app_version' => '1.0.0',
            'db'          => $db,
            'cache'       => $cache,
            'queue'       => config('queue.default'),
        ]);
    }
}
