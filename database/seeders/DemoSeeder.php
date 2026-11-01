<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BudgetRange;
use App\Enums\GuestCount;
use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Models\City;
use App\Models\CreditLedger;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Purchase;
use App\Models\ServiceRequest;
use App\Models\UnlockPack;
use App\Models\User;
use App\Models\WedRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sample data for the public demo: the accounts shown on the login pages, three
 * fictional providers per city, and requests in every state of the lifecycle.
 * All names, numbers and businesses are invented. Runs in production, where the
 * dev dependencies are absent: no factories or Faker here.
 */
class DemoSeeder extends Seeder
{
    private const BUSINESSES = [
        'NY' => ['Hudson Light Studio', 'Brooklyn Table Catering', 'Gotham Bloom Decor'],
        'LA' => ['Golden Coast Films', 'Sunset Sweets Bakery', 'Palm Court Events'],
        'CH' => ['Lakefront Photo Co', 'Windy City Sound', 'Loop Bridal Beauty'],
        'MI' => ['Biscayne Studio', 'Coral Table Catering', 'Ocean Drive Hall'],
        'PA' => ['Atelier Lumière', 'Maison Gourmande', 'Salons de la Seine'],
        'LY' => ['Studio Bellecour', 'Traiteur des Traboules', 'Son & Lumière Lyon'],
        'MA' => ['Vieux-Port Images', 'Pâtisserie du Panier', 'Domaine des Calanques'],
        'NI' => ['Promenade Photo', 'Riviera Beauté', 'Azur Décoration'],
    ];

    public function run(): void
    {
        $this->seedAdmin();

        $cities = City::all()->keyBy('code');
        $this->seedDirectory($cities);

        $newYork = $this->seedDemoProvider('provider', 'NY', 'Evergreen Weddings NYC', ['photo', 'video', 'decoration'], ['en', 'es'], '+12125550142');
        $paris = $this->seedDemoProvider('provider_fr', 'PA', 'Instants de Mariage Paris', ['photo', 'catering', 'pastry'], ['fr', 'en'], '+33612340142');

        $this->seedRequests($newYork, 'NY', [
            ['Emma', 'Manhattan', 'photo', BudgetRange::From20kTo35k, GuestCount::From100To200, 'Rooftop ceremony at sunset, natural photos.', 90],
            ['Olivia', 'Brooklyn', 'video', BudgetRange::From35kTo50k, GuestCount::From200To300, 'We would like a short highlight film.', 120],
            ['Sophia', 'Queens', 'decoration', BudgetRange::From10kTo20k, GuestCount::From100To200, 'Simple floral arches and table settings.', 150],
            ['Ava', 'Manhattan', 'photo', BudgetRange::From50kTo100k, GuestCount::From300To500, null, 200],
        ]);
        $this->seedRequests($paris, 'PA', [
            ['Claire', 'Le Marais', 'photo', BudgetRange::From20kTo35k, GuestCount::From100To200, 'Cérémonie civile puis cocktail, photos naturelles.', 100],
            ['Camille', 'Montmartre', 'catering', BudgetRange::From35kTo50k, GuestCount::From200To300, 'Menu de saison, options végétariennes.', 130],
            ['Léa', 'Saint-Germain', 'pastry', BudgetRange::From10kTo20k, GuestCount::From100To200, 'Pièce montée et table de desserts.', 170],
            ['Inès', 'Le Marais', 'photo', BudgetRange::From35kTo50k, GuestCount::From100To200, null, 210],
        ]);
    }

    private function seedAdmin(): void
    {
        $account = config('wedding.demo.accounts.admin');

        $user = User::updateOrCreate(['email' => $account['email']], [
            'name'              => 'Demo Admin',
            'password'          => Hash::make($account['password']),
            'role'              => UserRole::Admin->value,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['admin']);
    }

    private function seedDirectory($cities): void
    {
        $services = array_map(fn (ServiceType $s) => $s->value, ServiceType::cases());
        $next = 0;
        $number = 1000;

        foreach (self::BUSINESSES as $code => $names) {
            $city = $cities[$code] ?? null;
            if (! $city) {
                continue;
            }

            foreach ($names as $name) {
                $number++;
                $phone = $city->country === 'FR' ? '+3361234' . $number : '+1212555' . $number;
                $offered = [];
                for ($i = 0; $i < 3; $i++) {
                    $offered[] = $services[$next++ % count($services)];
                }

                $this->createProvider(
                    "provider{$number}@example.com",
                    bin2hex(random_bytes(16)),
                    $name,
                    $code,
                    $offered,
                    $city->country === 'FR' ? ['fr', 'en'] : ['en'],
                    $phone,
                    3,
                );
            }
        }
    }

    private function seedDemoProvider(string $key, string $city, string $name, array $services, array $languages, string $phone): User
    {
        $account = config("wedding.demo.accounts.{$key}");

        return $this->createProvider($account['email'], $account['password'], $name, $city, $services, $languages, $phone, 20);
    }

    private function createProvider(string $email, string $password, string $name, string $city, array $services, array $languages, string $phone, int $credits): User
    {
        $user = User::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'role'              => UserRole::Provider->value,
            'phone'             => $phone,
            'whatsapp'          => $phone,
            'city_code'         => $city,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['provider']);

        ProviderProfile::create([
            'user_id'              => $user->id,
            'business_name'        => $name,
            'business_description' => 'Fictional business created for the demo.',
            'business_type'        => 'llc',
            'primary_city_code'    => $city,
            'price_tier'           => 'mid',
            'min_price'            => 1500,
            'max_price'            => 6000,
            'years_experience'     => 6,
            'rating_avg'           => 4.6,
            'rating_count'         => 12,
            'languages'            => $languages,
            'verified'             => true,
            'verified_at'          => now()->subDays(30),
            'portfolio_url'        => 'https://example.com/portfolio',
            'onboarding_completed' => true,
            'credit_balance'       => $credits,
        ]);

        foreach ($services as $service) {
            ProviderService::firstOrCreate(['user_id' => $user->id, 'service_type' => $service]);
        }
        ProviderCoverage::firstOrCreate(['user_id' => $user->id, 'city_code' => $city], ['travel_fee' => 0]);

        CreditLedger::create([
            'user_id'       => $user->id,
            'delta'         => $credits,
            'balance_after' => $credits,
            'type'          => 'bonus',
            'notes'         => 'Welcome bonus',
        ]);

        return $user;
    }

    /**
     * One accepted request, one in a live round, the others open to all eligible
     * providers: the provider area then shows every state straight away.
     */
    private function seedRequests(User $provider, string $city, array $rows): void
    {
        foreach ($rows as $index => [$firstName, $district, $service, $budget, $guests, $description, $days]) {
            $wr = WedRequest::create([
                'status'            => RequestStatus::New->value,
                'source'            => 'form',
                'couple_first_name' => $firstName,
                'couple_whatsapp'   => $city === 'PA' ? '+3361234' . (2000 + $index) : '+1212555' . (2000 + $index),
                'couple_city_code'  => $city,
                'couple_district'   => $district,
                'wedding_date'      => now()->addDays($days)->toDateString(),
                'wedding_season'    => $this->seasonFor(now()->addDays($days)->month),
                'guest_count'       => $guests->value,
                'budget_range'      => $budget->value,
                'description'       => $description,
                'services_needed'   => [$service],
            ]);

            $attributes = ['wed_request_id' => $wr->id, 'service_type' => $service];

            ServiceRequest::create($attributes + match ($index) {
                0 => [
                    'status'                    => RequestStatus::Accepted->value,
                    'current_round'             => 1,
                    'round_started_at'          => now()->subHours(3),
                    'round_1_provider_ids'      => [$provider->id],
                    'all_notified_provider_ids' => [$provider->id],
                    'unlocked_by_user_id'       => $provider->id,
                    'unlocked_at'               => now()->subHours(2),
                    'unlocked_via'              => 'dashboard',
                    'credits_charged'           => 1,
                ],
                1 => [
                    'status'                    => RequestStatus::Round1->value,
                    'current_round'             => 1,
                    'round_started_at'          => now(),
                    'round_1_provider_ids'      => [$provider->id],
                    'all_notified_provider_ids' => [$provider->id],
                ],
                default => [
                    'status'           => RequestStatus::OpenMarketplace->value,
                    'current_round'    => 4,
                    'round_started_at' => now()->subHours(8),
                ],
            });

            if ($index === 0) {
                $this->recordAcceptance($provider);
            }
        }

        $this->recordPurchase($provider);
    }

    private function recordAcceptance(User $provider): void
    {
        $profile = $provider->providerProfile;
        $profile->update(['credit_balance' => $profile->credit_balance - 1, 'leads_accepted' => 1, 'leads_received' => 1, 'acceptance_rate' => 100]);

        CreditLedger::create([
            'user_id'       => $provider->id,
            'delta'         => -1,
            'balance_after' => $profile->credit_balance,
            'type'          => 'unlock',
            'notes'         => null,
        ]);
    }

    private function recordPurchase(User $provider): void
    {
        $pack = UnlockPack::where('slug', 'essential')->first();
        if (! $pack) {
            return;
        }

        $purchase = Purchase::create([
            'user_id'        => $provider->id,
            'unlock_pack_id' => $pack->id,
            'amount'         => (int) $pack->price,
            'currency'       => $provider->providerProfile->currency(),
            'unlocks_count'  => (int) $pack->unlocks_count,
            'status'         => 'paid',
            'gateway'        => 'demo',
            'payment_method' => 'online_demo',
        ]);
        $purchase->forceFill(['paid_at' => now()->subDay(), 'credits_granted_at' => now()->subDay()])->save();

        $profile = $provider->providerProfile;
        $profile->update(['credit_balance' => $profile->credit_balance + $pack->unlocks_count]);

        CreditLedger::create([
            'user_id'        => $provider->id,
            'delta'          => $pack->unlocks_count,
            'balance_after'  => $profile->credit_balance,
            'type'           => 'purchase',
            'reference_type' => Purchase::class,
            'reference_id'   => $purchase->id,
            'notes'          => null,
        ]);
    }

    private function seasonFor(int $month): string
    {
        return match (true) {
            in_array($month, [12, 1, 2], true) => 'winter',
            in_array($month, [3, 4, 5], true)  => 'spring',
            in_array($month, [6, 7, 8], true)  => 'summer',
            default                            => 'autumn',
        };
    }
}
