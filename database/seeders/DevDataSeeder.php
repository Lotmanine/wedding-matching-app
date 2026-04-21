<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Enums\Wilaya;
use App\Models\CreditLedger;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevDataSeeder extends Seeder
{
    /** Major wilayas for seeded providers. */
    private const MAJOR_WILAYAS = ['16', '31', '25', '23', '09', '19']; // Alger, Oran, Constantine, Annaba, Blida, Setif

    public function run(): void
    {
        $this->seedProviders(20);
        $this->seedWedRequests(10);
    }

    private function seedProviders(int $count): void
    {
        $serviceTypes = collect(ServiceType::cases())->map(fn ($s) => $s->value)->all();

        for ($i = 1; $i <= $count; $i++) {
            $wilaya = self::MAJOR_WILAYAS[array_rand(self::MAJOR_WILAYAS)];

            $user = User::create([
                'name'              => "Provider Dev {$i}",
                'email'             => "provider{$i}@zawajpro.dz",
                'password'          => Hash::make('password'),
                'role'              => UserRole::Provider->value,
                'phone'             => '+213' . str_pad((string) random_int(500000000, 799999999), 9, '0'),
                'whatsapp'          => '+213' . str_pad((string) random_int(500000000, 799999999), 9, '0'),
                'wilaya_code'       => $wilaya,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]);

            ProviderProfile::factory()->create([
                'user_id'             => $user->id,
                'primary_wilaya_code' => $wilaya,
            ]);

            // Assign 1-3 service types per provider
            $providerServices = array_unique((array) array_rand(array_flip($serviceTypes), min(count($serviceTypes), random_int(1, 3))));
            foreach ((array) $providerServices as $svc) {
                ProviderService::firstOrCreate(['user_id' => $user->id, 'service_type' => $svc]);
            }

            // Coverage: primary wilaya + 1-2 neighbouring
            ProviderCoverage::firstOrCreate(
                ['user_id' => $user->id, 'wilaya_code' => $wilaya],
                ['travel_fee_dzd' => 0]
            );
            $extraWilayas = array_filter(self::MAJOR_WILAYAS, fn ($w) => $w !== $wilaya);
            $extra = array_slice($extraWilayas, 0, random_int(1, 2));
            foreach ($extra as $w) {
                ProviderCoverage::firstOrCreate(
                    ['user_id' => $user->id, 'wilaya_code' => $w],
                    ['travel_fee_dzd' => random_int(1000, 8000)]
                );
            }

            // Credit ledger: initial bonus + a couple of transactions
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

            // Sync credit_balance on profile
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
            // Ensure 2-4 service requests per wedding
            $services = array_slice(array_unique($services), 0, random_int(2, min(4, max(2, count($services)))));
            if (count($services) < 2) {
                $all = array_diff(collect(ServiceType::cases())->map(fn ($s) => $s->value)->all(), $services);
                $services[] = $all[array_rand($all)];
            }

            foreach ($services as $svc) {
                ServiceRequest::create([
                    'wed_request_id' => $wr->id,
                    'service_type'   => $svc,
                    'status'         => RequestStatus::New->value,
                    'current_round'  => 0,
                ]);
            }
        }
    }
}
