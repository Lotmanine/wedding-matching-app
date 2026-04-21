<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Events\RoundTimedOut;
use App\Models\ServiceRequest;
use App\Services\Matching\RoundStateMachine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AdvanceTimedOutRounds extends Command
{
    protected $signature = 'zawaj:advance-rounds';

    protected $description = 'Advance service_requests whose current round has been open longer than the timeout';

    public function __construct(private readonly RoundStateMachine $stateMachine)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $timedOut = ServiceRequest::query()
            ->whereIn('status', [
                RequestStatus::Round1->value,
                RequestStatus::Round2->value,
                RequestStatus::Round3->value,
            ])
            ->whereNotNull('round_started_at')
            ->where('round_started_at', '<=', now()->subMinutes(RoundStateMachine::ROUND_TIMEOUT_MINUTES))
            ->where('expires_at', '>', now())
            ->whereNull('unlocked_by_user_id')
            ->get();

        $advanced = 0;
        foreach ($timedOut as $sr) {
            $previousRound = $sr->current_round;

            RoundTimedOut::dispatch($sr, $previousRound);

            $this->stateMachine->advanceRound($sr);

            foreach ($this->collectAffectedProviderIds($sr) as $uid) {
                Cache::forget("leads:count:{$uid}");
            }
            Cache::forget('leads:open_marketplace:version');

            $advanced++;
        }

        $this->info("Advanced {$advanced} timed-out round(s).");

        return self::SUCCESS;
    }

    /**
     * @return array<int>
     */
    private function collectAffectedProviderIds(ServiceRequest $sr): array
    {
        return collect([])
            ->merge((array) ($sr->all_notified_provider_ids ?? []))
            ->merge((array) ($sr->round_1_provider_ids ?? []))
            ->merge((array) ($sr->round_2_provider_ids ?? []))
            ->merge((array) ($sr->round_3_provider_ids ?? []))
            ->unique()
            ->values()
            ->all();
    }
}
