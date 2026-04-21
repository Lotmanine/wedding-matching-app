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
use App\Models\Wilaya;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CoupleRequestForm extends Component
{
    public int $step = 1;

    public string $couple_first_name = '';
    public string $couple_whatsapp = '';
    public string $couple_wilaya_code = '';
    public string $couple_commune = '';
    public ?string $wedding_date = null;
    public ?string $wedding_season = null;
    public string $guest_count = '';
    public string $budget_range = '';

    /** @var string[] */
    public array $services_needed = [];

    public string $description = '';
    public bool $acceptTerms = false;

    #[Computed]
    public function wilayas(): Collection
    {
        return Wilaya::active()->get();
    }

    #[Computed]
    public function availableCommunes(): Collection
    {
        if ($this->couple_wilaya_code === '') {
            return collect();
        }

        $wilaya = Wilaya::where('code', $this->couple_wilaya_code)->first();

        return $wilaya
            ? $wilaya->communes()->orderBy('name_fr')->get()
            : collect();
    }

    #[Computed]
    public function serviceTypes(): array
    {
        return ServiceType::cases();
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

    public function updatedCoupleWilayaCode(): void
    {
        $this->couple_commune = '';
    }

    public function updatedWeddingDate(): void
    {
        if (! empty($this->wedding_date)) {
            $this->wedding_season = $this->deriveSeason($this->wedding_date);
        }
    }

    private function deriveSeason(string $date): string
    {
        $month = (int) Carbon::parse($date)->format('n');

        return match (true) {
            in_array($month, [12, 1, 2], true)  => 'Hiver',
            in_array($month, [3, 4, 5], true)   => 'Printemps',
            in_array($month, [6, 7, 8], true)   => 'Été',
            default                             => 'Automne',
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rulesForStep(int $step): array
    {
        $whatsappRegex = '/^\+213[567]\d{8}$/';
        $wilayaCodes = Wilaya::pluck('code')->all();
        $svcValues = collect(ServiceType::cases())->pluck('value')->all();
        $gcValues = collect(GuestCount::cases())->pluck('value')->all();
        $brValues = collect(BudgetRange::cases())->pluck('value')->all();

        return match ($step) {
            1 => [
                'couple_first_name' => ['required', 'string', 'min:2', 'max:50'],
                'couple_whatsapp'   => ['required', 'string', 'regex:' . $whatsappRegex],
            ],
            2 => [
                'couple_wilaya_code' => ['required', 'string', 'in:' . implode(',', $wilayaCodes)],
                'couple_commune'     => ['nullable', 'string', 'max:120'],
            ],
            3 => [
                'wedding_date'   => ['nullable', 'date', 'after_or_equal:today', 'before:' . now()->addYears(2)->toDateString()],
                'wedding_season' => ['nullable', 'in:Printemps,Été,Automne,Hiver'],
                'guest_count'    => ['required', 'in:' . implode(',', $gcValues)],
            ],
            4 => [
                'budget_range'       => ['required', 'in:' . implode(',', $brValues)],
                'services_needed'    => ['required', 'array', 'min:1', 'max:8'],
                'services_needed.*'  => ['string', 'in:' . implode(',', $svcValues)],
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
                    'couple_wilaya_code' => $this->couple_wilaya_code,
                    'couple_commune'     => $this->couple_commune !== '' ? $this->couple_commune : null,
                    'wedding_date'       => $this->wedding_date ?: null,
                    'wedding_season'     => $this->wedding_season ?: null,
                    'guest_count'        => $this->guest_count,
                    'budget_range'       => $this->budget_range,
                    'description'        => $this->description !== '' ? $this->description : null,
                    'services_needed'    => array_values($this->services_needed),
                    'source'             => 'form',
                    'ip_address'         => request()->ip(),
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
