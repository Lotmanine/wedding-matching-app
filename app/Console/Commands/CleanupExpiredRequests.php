<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Models\WebhookDelivery;
use App\Models\WedRequest;
use App\Models\WhatsappMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupExpiredRequests extends Command
{
    protected $signature = 'zawaj:cleanup-expired';

    protected $description = 'Expire wed_requests past their validity, then erase the couple\'s personal data (ANPDP compliance)';

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

        $purged = $this->purgePersonalData();

        $count = count($uids);
        $log->info('Cleanup expired wed_requests run', [
            'count'    => $count,
            'wed_uids' => $uids,
            'purged'   => $purged,
            'ran_at'   => now()->toAtomString(),
        ]);

        $this->info("Cleaned up {$count} expired wedding request(s).");
        if ($count > 0) {
            $this->line('UIDs: ' . implode(', ', $uids));
        }
        $this->info("Erased personal data of {$purged['requests']} request(s), {$purged['whatsapp']} WhatsApp row(s), {$purged['deliveries']} webhook log(s).");

        return self::SUCCESS;
    }

    /**
     * Hiding an expired request is not deleting it: this erases what
     * identifies the couple and keeps only anonymous figures (wilaya,
     * services, budget range) for statistics.
     *
     * A request accepted by a provider is erased once that provider's own
     * access window has closed, so the lead they paid for stays usable for
     * the promised number of days.
     *
     * @return array{requests: int, whatsapp: int, deliveries: int}
     */
    private function purgePersonalData(): array
    {
        $days = (int) config('zawaj.anpdp.retention_days', 14);
        $cutoff = now()->subDays($days);

        $requests = WedRequest::withTrashed()
            ->whereNull('personal_data_purged_at')
            ->where('expires_at', '<=', now())
            // withTrashed: service requests are soft-deleted along with their request.
            ->whereDoesntHave('serviceRequests', fn ($q) => $q->withTrashed()->where('unlocked_at', '>', $cutoff))
            ->get();

        foreach ($requests as $wr) {
            DB::table('wed_requests')->where('id', $wr->id)->update([
                'couple_first_name'       => 'Supprimé',
                'couple_whatsapp'         => '',
                'couple_commune'          => null,
                'description'             => null,
                'ip_address'              => null,
                'user_agent'              => null,
                'personal_data_purged_at' => now(),
            ]);
        }

        // Copies of the same data kept in technical logs.
        $whatsapp = WhatsappMessage::where('role', 'couple')
            ->where('created_at', '<=', $cutoff)
            ->where('recipient_phone_e164', '!=', '')
            ->update(['recipient_phone_e164' => '', 'payload_snapshot' => null]);

        $deliveries = WebhookDelivery::where('created_at', '<=', $cutoff)->delete();

        return ['requests' => $requests->count(), 'whatsapp' => $whatsapp, 'deliveries' => $deliveries];
    }
}
