<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Models\Commune;
use App\Models\CreditLedger;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevDataSeeder extends Seeder
{
    /** Major-city wilaya codes for seeded providers: Alger, Oran, Constantine, Annaba, Blida, Sétif. */
    private const MAJOR_WILAYA_CODES = ['16', '31', '25', '23', '09', '19'];

    public function run(): void
    {
        $majorWilayas = Wilaya::whereIn('code', self::MAJOR_WILAYA_CODES)->get()->keyBy('code');
        if ($majorWilayas->isEmpty()) {
            $this->command?->warn('No wilayas seeded — skipping DevDataSeeder. Did WilayaSeeder run first?');

            return;
        }

        $this->seedProviders(20, $majorWilayas);
        $this->seedWedRequests(10);
    }

    private function seedProviders(int $count, $majorWilayas): void
    {
        $serviceTypes = collect(ServiceType::cases())->map(fn ($s) => $s->value)->all();

        for ($i = 1; $i <= $count; $i++) {
            $wilaya = $majorWilayas->random();
            $commune = Commune::forWilaya($wilaya->id)->inRandomOrder()->first();

            $user = User::create([
                'name'              => "Provider Dev {$i}",
                'email'             => "provider{$i}@zawajpro.dz",
                'password'          => Hash::make('password'),
                'role'              => UserRole::Provider->value,
                'phone'             => '+213' . str_pad((string) random_int(500000000, 799999999), 9, '0'),
                'whatsapp'          => '+213' . str_pad((string) random_int(500000000, 799999999), 9, '0'),
                'wilaya_code'       => $wilaya->code,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['provider']);

            $verified = random_int(1, 100) <= 60;
            ProviderProfile::factory()->create([
                'user_id'             => $user->id,
                'primary_wilaya_code' => $wilaya->code,
                'verified'            => $verified,
                'verified_at'         => $verified ? now()->subDays(random_int(1, 90)) : null,
                'portfolio_images'    => [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?w=800',
                    'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=800',
                    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800',
                ],
                'portfolio_url'       => 'https://instagram.com/' . strtolower("provider_dev_{$i}"),
            ]);

            $providerServices = (array) array_rand(
                array_flip($serviceTypes),
                min(count($serviceTypes), random_int(1, 3))
            );
            foreach ($providerServices as $svc) {
                ProviderService::firstOrCreate(['user_id' => $user->id, 'service_type' => $svc]);
            }

            ProviderCoverage::firstOrCreate(
                ['user_id' => $user->id, 'wilaya_code' => $wilaya->code],
                ['travel_fee_dzd' => 0]
            );
            $extraCodes = $majorWilayas->except($wilaya->code)->pluck('code')->shuffle()->take(random_int(1, 2));
            foreach ($extraCodes as $code) {
                ProviderCoverage::firstOrCreate(
                    ['user_id' => $user->id, 'wilaya_code' => $code],
                    ['travel_fee_dzd' => random_int(1000, 8000)]
                );
            }

            $balance = 3;
            CreditLedger::create([
                'user_id'       => $user->id,
                'delta'         => 3,
                'balance_after' => $balance,
                'type'          => 'bonus',
                'notes'         => 'Bonus de bienvenue',
            ]);

            $txCount = random_int(0, 4);
            for ($j = 0; $j < $txCount; $j++) {
                $type = ['purchase', 'unlock', 'bonus'][array_rand(['purchase', 'unlock', 'bonus'])];
                $delta = match ($type) {
                    'purchase' => [3, 6, 10][array_rand([3, 6, 10])],
                    'unlock'   => -1,
                    'bonus'    => random_int(1, 2),
                };
                $balance = max(0, $balance + $delta);
                CreditLedger::create([
                    'user_id'       => $user->id,
                    'delta'         => $delta,
                    'balance_after' => $balance,
                    'type'          => $type,
                    'notes'         => null,
                ]);
            }

            $user->providerProfile->update(['credit_balance' => $balance]);
        }
    }

    private function seedWedRequests(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $wr = WedRequest::factory()->create();

            $services = (array) $wr->services_needed;
            if (empty($services)) {
                $services = [ServiceType::Photo->value];
            }
            $services = array_slice(array_unique($services), 0, random_int(2, min(4, max(2, count($services)))));
            if (count($services) < 2) {
                $all = array_diff(collect(ServiceType::cases())->map(fn ($s) => $s->value)->all(), $services);
                $services[] = $all[array_rand($all)];
            }

            foreach ($services as $svc) {
                $this->createDistributedServiceRequest($wr->id, $svc);
            }
        }
    }

    private function createDistributedServiceRequest(int $wedRequestId, string $serviceType): void
    {
        $providerIds = User::role('provider')->inRandomOrder()->limit(6)->pluck('id')->all();
        $pick = fn (int $n) => array_slice($providerIds, 0, min($n, count($providerIds)));

        $roll = random_int(1, 100);

        $attrs = [
            'wed_request_id' => $wedRequestId,
            'service_type'   => $serviceType,
            'current_round'  => 0,
            'status'         => RequestStatus::New->value,
        ];

        $timedOutChance = random_int(1, 10) === 1;
        $r1Minutes = $timedOutChance ? random_int(130, 360) : random_int(1, 60);

        if ($roll <= 30) {
            $ids = $pick(random_int(2, 3));
            $attrs = array_merge($attrs, [
                'status'                    => RequestStatus::Round1->value,
                'current_round'             => 1,
                'round_started_at'          => now()->subMinutes($r1Minutes),
                'round_1_provider_ids'      => $ids,
                'all_notified_provider_ids' => $ids,
            ]);
        } elseif ($roll <= 50) {
            $r1 = $pick(random_int(2, 3));
            $r2 = array_slice($providerIds, count($r1), random_int(2, 3));
            $attrs = array_merge($attrs, [
                'status'                    => RequestStatus::Round2->value,
                'current_round'             => 2,
                'round_started_at'          => now()->subMinutes(random_int(1, 90)),
                'round_1_provider_ids'      => $r1,
                'round_2_provider_ids'      => $r2,
                'all_notified_provider_ids' => array_values(array_unique(array_merge($r1, $r2))),
            ]);
        } elseif ($roll <= 60) {
            $r1 = $pick(2);
            $r2 = array_slice($providerIds, 2, 2);
            $r3 = array_slice($providerIds, 4, 2);
            $attrs = array_merge($attrs, [
                'status'                    => RequestStatus::Round3->value,
                'current_round'             => 3,
                'round_started_at'          => now()->subMinutes(random_int(1, 120)),
                'round_1_provider_ids'      => $r1,
                'round_2_provider_ids'      => $r2,
                'round_3_provider_ids'      => $r3,
                'all_notified_provider_ids' => array_values(array_unique(array_merge($r1, $r2, $r3))),
            ]);
        } elseif ($roll <= 75) {
            $attrs = array_merge($attrs, [
                'status'                    => RequestStatus::OpenMarketplace->value,
                'current_round'             => 4,
                'round_started_at'          => now()->subMinutes(random_int(10, 240)),
                'all_notified_provider_ids' => $providerIds,
            ]);
        } else {
            // status=new, the remaining 25%
        }

        // Rare: 1 in 20 marked as already unlocked (won't appear in feeds)
        if (random_int(1, 20) === 1 && ! empty($providerIds)) {
            $attrs['unlocked_by_user_id'] = $providerIds[0];
            $attrs['unlocked_at']         = now()->subMinutes(random_int(1, 300));
            $attrs['unlocked_via']        = 'dashboard';
            $attrs['credits_charged']     = 1;
        }

        ServiceRequest::create($attrs);
    }
}
