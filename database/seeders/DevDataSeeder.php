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

            ProviderProfile::factory()->create([
                'user_id'             => $user->id,
                'primary_wilaya_code' => $wilaya->code,
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
