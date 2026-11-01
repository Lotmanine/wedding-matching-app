<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Matching\LeadAcceptanceService;
use App\Services\Matching\RoundStateMachine;
use Illuminate\Console\Command;

class SimulateMatching extends Command
{
    protected $signature = 'wedding:simulate-matching {service_uid}';

    protected $description = 'Walk a ServiceRequest through round 1 with random providers (interactive)';

    public function __construct(
        private readonly RoundStateMachine $stateMachine,
        private readonly LeadAcceptanceService $acceptance,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $uid = (string) $this->argument('service_uid');
        $sr = ServiceRequest::where('service_uid', $uid)->with('wedRequest')->first();

        if (! $sr) {
            $this->error("ServiceRequest {$uid} not found.");

            return self::FAILURE;
        }

        $this->line("Lead: {$sr->service_uid} — {$sr->service_type?->label()} — status={$sr->status?->value}");

        $coupleCity = $sr->wedRequest?->couple_city_code;
        $candidates = User::role('provider')
            ->whereHas('providerServices', fn ($q) => $q->where('service_type', $sr->service_type?->value))
            ->whereHas('providerCoverage', fn ($q) => $q->where('city_code', (string) $coupleCity))
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true))
            ->inRandomOrder()
            ->limit((int) config('wedding.matching.providers_per_round', 3))
            ->get();

        if ($candidates->isEmpty()) {
            $this->error('No eligible verified providers found for this lead.');

            return self::FAILURE;
        }

        $ids = $candidates->pluck('id')->all();
        $this->info('Starting round 1 with providers: ' . implode(', ', $ids));
        $this->stateMachine->startRound($sr, 1, $ids);
        $sr->refresh();

        foreach ($candidates as $cand) {
            $profile = $cand->providerProfile;
            $this->line("  - {$cand->id}: {$cand->email} ({$profile?->business_name}) · credits={$profile?->credit_balance}");
        }

        if (! $this->confirm('Simulate an accept by the first candidate?', false)) {
            $this->line('Aborted. Lead left at round 1.');

            return self::SUCCESS;
        }

        $result = $this->acceptance->accept($candidates->first(), $sr, 'simulation');
        $this->line("Result: {$result->status} — {$result->getMessage()}");

        return self::SUCCESS;
    }
}
