<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Models\WedRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredRequests extends Command
{
    protected $signature = 'zawaj:cleanup-expired';

    protected $description = 'Soft-delete wed_requests past their 14-day expiry (ANPDP compliance)';

    public function handle(): int
    {
        $expired = WedRequest::query()
            ->where('expires_at', '<=', now())
            ->whereNull('deleted_at')
            ->get();

        $log = Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/cleanup.log'),
        ]);

        $uids = [];
        foreach ($expired as $wr) {
            $wr->status = RequestStatus::Expired->value;
            $wr->save();
            $wr->delete();
            $uids[] = $wr->wed_uid;
        }

        $count = count($uids);
        $log->info('Cleanup expired wed_requests run', [
            'count'    => $count,
            'wed_uids' => $uids,
            'ran_at'   => now()->toAtomString(),
        ]);

        $this->info("Cleaned up {$count} expired wedding request(s).");
        if ($count > 0) {
            $this->line('UIDs: ' . implode(', ', $uids));
        }

        return self::SUCCESS;
    }
}
