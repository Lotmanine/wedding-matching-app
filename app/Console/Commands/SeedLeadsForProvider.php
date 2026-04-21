<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RequestStatus;
use App\Models\ProviderCoverage;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Illuminate\Console\Command;

class SeedLeadsForProvider extends Command
{
    protected $signature = 'zawaj:seed-leads-for-provider {email} {count=5}';

    protected $description = 'Create N WedRequest+ServiceRequest pairs that are eligible (round_1) for the given provider';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $count = max(1, (int) $this->argument('count'));

        $user = User::where('email', $email)->with(['providerServices', 'providerCoverage'])->first();
        if (! $user) {
            $this->error("No user found with email {$email}");

            return self::FAILURE;
        }

        $services = $user->providerServices->pluck('service_type')
            ->map(fn ($v) => is_object($v) && property_exists($v, 'value') ? $v->value : (string) $v)
            ->filter()
            ->values()
            ->all();
        $coverage = $user->providerCoverage->pluck('wilaya_code')
            ->map(fn ($v) => is_object($v) && property_exists($v, 'value') ? $v->value : (string) $v)
            ->filter()
            ->values()
            ->all();

        if (empty($services) || empty($coverage)) {
            $this->error('Provider has no services or coverage defined.');

            return self::FAILURE;
        }

        $otherProviderIds = User::role('provider')
            ->where('id', '!=', $user->id)
            ->inRandomOrder()
            ->limit(4)
            ->pluck('id')
            ->all();

        for ($i = 0; $i < $count; $i++) {
            $service = $services[array_rand($services)];
            $wilaya = $coverage[array_rand($coverage)];

            $wr = WedRequest::factory()->create([
                'couple_wilaya_code' => $wilaya,
                'services_needed'    => [$service],
                'status'             => RequestStatus::New->value,
            ]);

            $roundIds = array_slice(array_merge([$user->id], $otherProviderIds), 0, 3);
            shuffle($roundIds);

            ServiceRequest::create([
                'wed_request_id'            => $wr->id,
                'service_type'              => $service,
                'status'                    => RequestStatus::Round1->value,
                'current_round'             => 1,
                'round_started_at'          => now(),
                'round_1_provider_ids'      => $roundIds,
                'all_notified_provider_ids' => $roundIds,
            ]);
        }

        $this->info("Created {$count} eligible lead(s) for {$email}");

        return self::SUCCESS;
    }
}
