<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class N8nWebhookController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function error(Request $request): JsonResponse
    {
        // Guard the public endpoint with a shared secret when configured, so an
        // anonymous caller can't pollute the audit log or trip ops alerts.
        $secret = (string) config('zawaj.n8n.webhook_secret', '');
        if ($secret !== '' && ! hash_equals($secret, (string) $request->header('X-Zawaj-Token', ''))) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $payload = $request->all();

        $line = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/n8n-errors.log'),
        ]);
        $line->warning('n8n workflow error reported', $payload);

        $this->audit->log('n8n.error', null, [
            'workflow' => $payload['workflow'] ?? null,
            'execution_id' => $payload['execution_id'] ?? null,
            'failed_node' => $payload['failed_node'] ?? null,
            'error_message' => $payload['error_message'] ?? null,
        ]);

        $recent = AuditLog::where('action', 'n8n.error')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return response()->json([
            'received' => true,
            'recent_1h' => $recent,
            'alert_threshold' => $recent >= 5,
        ]);
    }
}
