<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(string $action, ?Model $subject = null, array $metadata = []): AuditLog
    {
        $request = request();

        return AuditLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id'   => $subject?->getKey(),
            'metadata'     => $metadata,
            'ip_address'   => $request?->ip(),
            'user_agent'   => $request ? substr((string) $request->userAgent(), 0, 1000) : null,
        ]);
    }
}
