<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\LeadAccepted;
use App\Events\LeadOpenedToMarketplace;
use App\Events\RoundStarted;
use App\Models\ProviderProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Support\Whatsapp\SignedButtonPayload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SmokeWorkflow2 extends Command
{
    protected $signature = 'wedding:smoke:workflow-2';

    protected $description = 'Smoke-test Workflow 2 plumbing — fires the 3 events in-process and asserts payload + signed token roundtrip.';

    private int $issues = 0;

    public function handle(): int
    {
        $sr = ServiceRequest::with('wedRequest')->latest('id')->first();
        if (! $sr) {
            $this->error('No ServiceRequest in DB — run `php artisan migrate:fresh --seed --force` first.');

            return self::FAILURE;
        }

        $providers = User::role('provider')
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true))
            ->where('status', 'active')
            ->limit(3)
            ->pluck('id')
            ->all();

        if (count($providers) < 1) {
            $this->error('No verified providers available — seed dev data first.');

            return self::FAILURE;
        }

        $winner = User::role('provider')->where('status', 'active')->first();

        Bus::fake();

        // Fire round.started
        RoundStarted::dispatch($sr, 1, $providers);
        $this->check('RoundStarted listener executes without error', true);

        // Fire lead.accepted
        LeadAccepted::dispatch($sr, $winner);
        $this->check('LeadAccepted listener executes without error', true);

        // Fire lead.opened_to_marketplace
        LeadOpenedToMarketplace::dispatch($sr);
        $this->check('LeadOpenedToMarketplace listener executes without error', true);

        // Signed button roundtrip
        $token = SignedButtonPayload::encode($sr->service_uid, $providers[0], 'accept');
        $decoded = SignedButtonPayload::decode($token);
        $this->check(
            'Signed button token roundtrip',
            $decoded['service_request_uid'] === $sr->service_uid
                && $decoded['provider_id'] === $providers[0]
                && $decoded['intent'] === 'accept',
        );
        $this->check('Token fits Meta 256-char quick-reply limit', strlen($token) <= 256);
        $this->line("    token length: " . strlen($token) . ' chars');

        if ($this->issues > 0) {
            $this->error("{$this->issues} smoke check(s) failed.");

            return self::FAILURE;
        }

        $this->info('Workflow 2 smoke test passed.');

        return self::SUCCESS;
    }

    private function check(string $name, bool $ok): void
    {
        if ($ok) {
            $this->line("<fg=green>✓</> {$name}");
        } else {
            $this->line("<fg=red>✗</> {$name}");
            $this->issues++;
        }
    }
}
