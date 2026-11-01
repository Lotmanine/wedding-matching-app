<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\BudgetRange;
use App\Enums\GuestCount;
use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Events\WedRequestSubmitted;
use App\Models\ServiceRequest;
use App\Models\WedRequest;
use App\Models\City;
use App\Support\PhoneFormat;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\IpUtils;

class CoupleRequestForm extends Component
{
    public int $step = 1;

    public string $couple_first_name = '';
    public string $couple_whatsapp = '';
    public string $couple_city_code = '';
    public string $couple_district = '';
    public ?string $wedding_date = null;
    public ?string $wedding_season = null;
    public string $guest_count = '';
    public string $budget_range = '';

    /** @var string[] */
    public array $services_needed = [];

    public string $description = '';
    public bool $acceptTerms = false;

    #[Computed]
    public function cities(): Collection
    {
        return City::active()->get();
    }

    #[Computed]
    public function availableDistricts(): Collection
    {
        if ($this->couple_city_code === '') {
            return collect();
        }

        $city = City::where('code', $this->couple_city_code)->first();

        return $city
            ? $city->districts()->orderBy('name')->get()
            : collect();
    }

    #[Computed]
    public function serviceTypes(): array
    {
        // Only services currently activated in the admin catalog.
        return ServiceType::activeCases();
    }

    #[Computed]
    public function guestCounts(): array
    {
        return GuestCount::cases();
    }

    #[Computed]
    public function budgetRanges(): array
    {
        return BudgetRange::cases();
    }

    /**
     * Services whose quotes depend on the headcount. The "nombre d'invités"
     * question is only shown (and required) when at least one of these is
     * selected — a photographer or DJ doesn't need it.
     *
     * @var list<string>
     */
    public const GUEST_COUNT_SERVICES = ['catering', 'venue'];

    #[Computed]
    public function needsGuestCount(): bool
    {
        return count(array_intersect($this->services_needed, self::GUEST_COUNT_SERVICES)) > 0;
    }

    public function updatedCoupleCityCode(): void
    {
        $this->couple_district = '';
    }

    public function updatedWeddingDate(): void
    {
        if (! empty($this->wedding_date)) {
            $this->wedding_season = $this->deriveSeason($this->wedding_date);
        }
    }

    public function updatedServicesNeeded(): void
    {
        // If the couple no longer needs a headcount-dependent service, drop any
        // previously chosen guest count so we don't submit a stale value.
        if (! $this->needsGuestCount()) {
            $this->guest_count = '';
        }
    }

    private function deriveSeason(string $date): string
    {
        $month = (int) Carbon::parse($date)->format('n');

        return match (true) {
            in_array($month, [12, 1, 2], true)  => 'winter',
            in_array($month, [3, 4, 5], true)   => 'spring',
            in_array($month, [6, 7, 8], true)   => 'summer',
            default                             => 'autumn',
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rulesForStep(int $step): array
    {
        $cityCodes = City::active()->pluck('code')->all();
        $svcValues = ServiceType::activeValues();
        $gcValues = collect(GuestCount::cases())->pluck('value')->all();
        $brValues = collect(BudgetRange::cases())->pluck('value')->all();

        return match ($step) {
            1 => [
                'couple_first_name' => ['required', 'string', 'min:2', 'max:50'],
                'couple_whatsapp'   => ['required', 'string', 'regex:' . PhoneFormat::REGEX],
            ],
            2 => [
                'couple_city_code' => ['required', 'string', 'in:' . implode(',', $cityCodes)],
                'couple_district'     => ['nullable', 'string', 'max:120'],
            ],
            3 => [
                'budget_range'       => ['required', 'in:' . implode(',', $brValues)],
                'services_needed'    => ['required', 'array', 'min:1', 'max:8'],
                'services_needed.*'  => ['string', 'in:' . implode(',', $svcValues)],
            ],
            4 => [
                // Wedding date is now required. Guest count is required only when
                // a headcount-dependent service (catering / venue) is selected.
                'wedding_date'   => ['required', 'date', 'after_or_equal:today', 'before:' . now()->addYears(2)->toDateString()],
                'wedding_season' => ['nullable', 'in:' . implode(',', WedRequest::SEASONS)],
                'guest_count'    => [$this->needsGuestCount() ? 'required' : 'nullable', 'in:' . implode(',', $gcValues)],
            ],
            5 => [
                'description' => ['nullable', 'string', 'max:500'],
                'acceptTerms' => ['accepted'],
            ],
            default => [],
        };
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step));

        if ($this->step < 5) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $n): void
    {
        if ($n >= 1 && $n < $this->step) {
            $this->step = $n;
        }
    }

    public function submit()
    {
        $key = 'couple-form:' . (request()->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError('submission', __('form.rate_limit', ['minutes' => $minutes]));

            return null;
        }

        $rules = [];
        for ($i = 1; $i <= 5; $i++) {
            $rules = array_merge($rules, $this->rulesForStep($i));
        }
        $this->validate($rules);

        RateLimiter::hit($key, 3600);

        if (! empty($this->wedding_date)) {
            $this->wedding_season = $this->deriveSeason($this->wedding_date);
        }

        try {
            $wedRequest = DB::transaction(function () {
                $wr = WedRequest::create([
                    'status'             => RequestStatus::New->value,
                    'couple_first_name'  => $this->couple_first_name,
                    'couple_whatsapp'    => $this->couple_whatsapp,
                    'couple_city_code' => $this->couple_city_code,
                    'couple_district'     => $this->couple_district !== '' ? $this->couple_district : null,
                    'wedding_date'       => $this->wedding_date ?: null,
                    'wedding_season'     => $this->wedding_season ?: null,
                    'guest_count'        => $this->guest_count !== '' ? $this->guest_count : null,
                    'budget_range'       => $this->budget_range,
                    'description'        => $this->description !== '' ? $this->description : null,
                    'services_needed'    => array_values($this->services_needed),
                    'source'             => 'form',
                    // Stored anonymised (last IPv4 byte / last 80 IPv6 bits zeroed),
                    // as the privacy policy states: enough to spot abuse, not a person.
                    'ip_address'         => request()->ip() ? IpUtils::anonymize((string) request()->ip()) : null,
                    'user_agent'         => substr((string) request()->userAgent(), 0, 1000) ?: null,
                ]);

                foreach ($this->services_needed as $svc) {
                    ServiceRequest::create([
                        'wed_request_id' => $wr->id,
                        'service_type'   => $svc,
                        'status'         => RequestStatus::New->value,
                        'current_round'  => 0,
                    ]);
                }

                return $wr;
            });
        } catch (\Throwable $e) {
            report($e);
            $this->addError('submission', __('form.submission_failed'));

            return null;
        }

        WedRequestSubmitted::dispatch($wedRequest);

        return redirect()->route('couple.request.confirmation', ['wedUid' => $wedRequest->wed_uid]);
    }

    public function render()
    {
        return view('livewire.couple-request-form');
    }
}
