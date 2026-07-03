<?php

declare(strict_types=1);

namespace App\Livewire\Provider;

use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Events\ProviderRegistered;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class RegistrationForm extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1 — Compte
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $full_name = '';
    public string $phone = '';
    public string $whatsapp = '';
    public bool $accept_terms = false;
    public bool $accept_anpdp = false;

    // Step 2 — Entreprise
    public string $business_name = '';
    public string $business_type = '';
    public ?string $trade_register = null;
    public ?int $years_experience = null;
    public string $business_description = '';
    public string $primary_wilaya_code = '';
    public string $price_tier = '';
    public ?int $min_price = null;
    public ?int $max_price = null;

    /** @var string[] */
    public array $languages = [];

    // Step 3 — Services
    /** @var string[] */
    public array $services = [];

    // Step 4 — Couverture
    /** @var string[] */
    public array $coverage_wilayas = [];

    /** @var array<string, int> */
    public array $travel_fees = [];

    // Step 5 — Portfolio
    /** @var string[] */
    public array $portfolio_urls = [''];

    public array $portfolio_images = [];

    public $logo_upload = null;

    public function updatedPhone(): void
    {
        if ($this->whatsapp === '') {
            $this->whatsapp = $this->phone;
        }
    }

    public function updatedPrimaryWilayaCode(): void
    {
        if ($this->primary_wilaya_code !== '' && ! in_array($this->primary_wilaya_code, $this->coverage_wilayas, true)) {
            $this->coverage_wilayas[] = $this->primary_wilaya_code;
            $this->travel_fees[$this->primary_wilaya_code] = 0;
        }
    }

    public function addPortfolioUrl(): void
    {
        if (count($this->portfolio_urls) < 5) {
            $this->portfolio_urls[] = '';
        }
    }

    public function removePortfolioUrl(int $index): void
    {
        if (isset($this->portfolio_urls[$index]) && count($this->portfolio_urls) > 1) {
            unset($this->portfolio_urls[$index]);
            $this->portfolio_urls = array_values($this->portfolio_urls);
        }
    }

    public function toggleCoverageWilaya(string $code): void
    {
        if ($code === $this->primary_wilaya_code) {
            return;
        }

        if (in_array($code, $this->coverage_wilayas, true)) {
            $this->coverage_wilayas = array_values(array_filter($this->coverage_wilayas, fn ($c) => $c !== $code));
            unset($this->travel_fees[$code]);
        } else {
            $this->coverage_wilayas[] = $code;
            $this->travel_fees[$code] = $this->travel_fees[$code] ?? 0;
        }
    }

    #[Computed]
    public function wilayas(): Collection
    {
        return Wilaya::active()->get();
    }

    #[Computed]
    public function serviceTypes(): array
    {
        // Only services currently activated in the admin catalog.
        return ServiceType::activeCases();
    }

    protected function rulesForStep(int $step): array
    {
        $whatsappRegex = '/^\+213[567]\d{8}$/';

        return match ($step) {
            1 => [
                'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
                'password'              => ['required', 'string', 'min:8', 'confirmed'],
                'full_name'             => ['required', 'string', 'min:2', 'max:100'],
                'phone'                 => ['required', 'string', 'regex:' . $whatsappRegex],
                'whatsapp'              => ['required', 'string', 'regex:' . $whatsappRegex],
                'accept_terms'          => ['accepted'],
                'accept_anpdp'          => ['accepted'],
            ],
            2 => [
                'business_name'        => ['required', 'string', 'min:2', 'max:100'],
                'business_type'        => ['required', 'in:auto-entrepreneur,sarl,eurl,particulier'],
                'trade_register'       => ['nullable', 'string', 'max:50'],
                'years_experience'     => ['nullable', 'integer', 'min:0', 'max:60'],
                'business_description' => ['nullable', 'string', 'max:500'],
                'primary_wilaya_code'  => ['required', 'exists:wilayas,code'],
                'price_tier'           => ['required', 'in:budget,mid,premium,luxury'],
                'min_price'            => ['nullable', 'integer', 'min:0'],
                'max_price'            => ['nullable', 'integer', 'min:0', 'gte:min_price'],
                'languages'            => ['required', 'array', 'min:1'],
                'languages.*'          => ['in:fr,ar,en,ber'],
            ],
            3 => [
                'services'   => ['required', 'array', 'min:1'],
                'services.*' => ['in:' . implode(',', ServiceType::activeValues())],
            ],
            4 => [
                'coverage_wilayas'   => ['required', 'array', 'min:1'],
                'coverage_wilayas.*' => ['exists:wilayas,code'],
                'travel_fees'        => ['array'],
                'travel_fees.*'      => ['integer', 'min:0'],
            ],
            5 => [
                'portfolio_urls'   => ['required', 'array', 'min:1', 'max:5'],
                'portfolio_urls.*' => ['required', 'url', 'max:500'],
                'portfolio_images' => ['nullable', 'array', 'max:10'],
                'portfolio_images.*' => ['image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
                'logo_upload'      => ['nullable', 'image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
            ],
            default => [],
        };
    }

    public function nextStep(): void
    {
        $rules = $this->rulesForStep($this->step);

        if ($this->step === 5) {
            $rules['portfolio_urls'] = ['required', 'array', 'min:1', 'max:5'];
            $rules['portfolio_urls.*'] = ['required', 'url', 'max:500'];
        }

        $this->validate($rules);

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

    public function submit()
    {
        $rules = [];
        for ($i = 1; $i <= 5; $i++) {
            $rules = array_merge($rules, $this->rulesForStep($i));
        }
        $this->validate($rules);

        $storedPaths = [];

        try {
            $user = DB::transaction(function () use (&$storedPaths) {
                $user = User::create([
                    'name'              => $this->full_name,
                    'email'             => $this->email,
                    'password'          => Hash::make($this->password),
                    'role'              => UserRole::Provider->value,
                    'phone'             => $this->phone,
                    'whatsapp'          => $this->whatsapp,
                    'wilaya_code'       => $this->primary_wilaya_code,
                    'status'            => 'pending_verification',
                ]);

                $user->syncRoles(['provider']);

                $logoUrl = null;
                if ($this->logo_upload instanceof TemporaryUploadedFile) {
                    $logoPath = $this->logo_upload->storeAs(
                        "providers/{$user->id}",
                        'logo.' . $this->logo_upload->getClientOriginalExtension(),
                        'public',
                    );
                    $storedPaths[] = $logoPath;
                    $logoUrl = Storage::url($logoPath);
                }

                $portfolioLocalUrls = [];
                foreach ((array) $this->portfolio_images as $idx => $img) {
                    if (! $img instanceof TemporaryUploadedFile) {
                        continue;
                    }
                    $path = $img->store("providers/{$user->id}/portfolio", 'public');
                    $storedPaths[] = $path;
                    $portfolioLocalUrls[] = Storage::url($path);
                }

                $profile = ProviderProfile::create([
                    'user_id'              => $user->id,
                    'business_name'        => $this->business_name,
                    'business_description' => $this->business_description ?: null,
                    'business_type'        => $this->business_type,
                    'trade_register'       => $this->trade_register ?: null,
                    'primary_wilaya_code'  => $this->primary_wilaya_code,
                    'price_tier'           => $this->price_tier,
                    'min_price'            => $this->min_price,
                    'max_price'            => $this->max_price,
                    'years_experience'     => $this->years_experience,
                    'portfolio_url'        => $this->portfolio_urls[0] ?? null,
                    'portfolio_images'     => array_values(array_filter($portfolioLocalUrls + array_filter($this->portfolio_urls))),
                    'logo_url'             => $logoUrl,
                    'languages'            => array_values($this->languages),
                    'verified'             => false,
                    'onboarding_completed' => true,
                ]);

                foreach (array_unique($this->services) as $svc) {
                    ProviderService::firstOrCreate([
                        'user_id'      => $user->id,
                        'service_type' => $svc,
                    ]);
                }

                if (! in_array($this->primary_wilaya_code, $this->coverage_wilayas, true)) {
                    $this->coverage_wilayas[] = $this->primary_wilaya_code;
                }

                foreach (array_unique($this->coverage_wilayas) as $code) {
                    $fee = $code === $this->primary_wilaya_code ? 0 : (int) ($this->travel_fees[$code] ?? 0);
                    ProviderCoverage::updateOrCreate(
                        ['user_id' => $user->id, 'wilaya_code' => $code],
                        ['travel_fee_dzd' => $fee],
                    );
                }

                ProviderRegistered::dispatch($user, $profile);

                return $user;
            });
        } catch (\Throwable $e) {
            foreach ($storedPaths as $p) {
                try {
                    Storage::disk('public')->delete($p);
                } catch (\Throwable) {
                    // best-effort — orphan cleanup command catches anything left
                }
            }
            report($e);
            $this->addError('submission', __('provider.registration.failed'));

            return null;
        }

        Auth::login($user);
        session()->flash('status', __('provider.registration.success'));

        return redirect()->route('provider.dashboard');
    }

    public function render()
    {
        return view('livewire.provider.registration-form');
    }
}
