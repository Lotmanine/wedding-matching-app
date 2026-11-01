<?php

declare(strict_types=1);

namespace App\Livewire\Provider;

use App\Enums\ServiceType;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\City;
use App\Support\PhoneFormat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ProfileEditor extends Component
{
    use WithFileUploads;

    // Account
    public string $full_name = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $email = '';

    // Business
    public string $business_name = '';
    public string $business_type = '';
    public ?string $trade_register = null;
    public ?int $years_experience = null;
    public string $business_description = '';
    public string $primary_city_code = '';
    public string $price_tier = '';
    public ?int $min_price = null;
    public ?int $max_price = null;

    /** @var string[] */
    public array $languages = [];

    // Services
    /** @var string[] */
    public array $services = [];

    // Coverage
    /** @var string[] */
    public array $coverage_cities = [];

    /** @var array<string, int> */
    public array $travel_fees = [];

    // Portfolio
    /** @var string[] */
    public array $portfolio_urls = [];

    /** @var string[] */
    public array $portfolio_images_existing = [];

    public array $portfolio_images_new = [];

    public ?string $logo_url_existing = null;

    public $logo_new = null;

    public function mount(): void
    {
        $user = Auth::user();
        $profile = $user->providerProfile;

        $this->full_name   = $user->name;
        $this->phone       = (string) $user->phone;
        $this->whatsapp    = (string) $user->whatsapp;
        $this->email       = $user->email;

        if ($profile) {
            $this->business_name        = $profile->business_name;
            $this->business_type        = (string) $profile->business_type;
            $this->trade_register       = $profile->trade_register;
            $this->years_experience     = $profile->years_experience;
            $this->business_description = (string) $profile->business_description;
            $primary = $profile->getAttributes()['primary_city_code'] ?? '';
            $this->primary_city_code  = is_string($primary) ? $primary : (string) $profile->primary_city_code;
            $this->price_tier           = (string) $profile->price_tier;
            $this->min_price            = $profile->min_price;
            $this->max_price            = $profile->max_price;
            $this->languages            = (array) ($profile->languages ?? []);
            $this->portfolio_urls       = $profile->portfolio_url ? [$profile->portfolio_url] : [''];
            $this->portfolio_images_existing = (array) ($profile->portfolio_images ?? []);
            $this->logo_url_existing    = $profile->logo_url;
        }

        $this->services = $user->providerServices->pluck('service_type')->map(fn ($v) => $v instanceof ServiceType ? $v->value : (string) $v)->all();

        $coverage = $user->providerCoverage;
        $this->coverage_cities = $coverage->pluck('city_code')->map(fn ($v) => (string) $v)->all();
        $this->travel_fees = $coverage->mapWithKeys(fn ($c) => [(string) $c->city_code => (int) $c->travel_fee])->all();

        if ($this->primary_city_code && ! in_array($this->primary_city_code, $this->coverage_cities, true)) {
            $this->coverage_cities[] = $this->primary_city_code;
            $this->travel_fees[$this->primary_city_code] = 0;
        }
    }

    #[Computed]
    public function cities(): Collection
    {
        return City::active()->get();
    }

    #[Computed]
    public function serviceTypes(): array
    {
        // Active catalog services, plus any the provider already offers —
        // deactivating a service must not strip it from existing profiles.
        $active = ServiceType::activeCases();
        $ownedValues = array_diff($this->services ?? [], array_map(fn ($c) => $c->value, $active));
        $owned = array_values(array_filter(array_map(fn ($v) => ServiceType::tryFrom((string) $v), $ownedValues)));

        return array_merge($active, $owned);
    }

    public function toggleCoverageCity(string $code): void
    {
        if ($code === $this->primary_city_code) {
            return;
        }

        if (in_array($code, $this->coverage_cities, true)) {
            $this->coverage_cities = array_values(array_filter($this->coverage_cities, fn ($c) => $c !== $code));
            unset($this->travel_fees[$code]);
        } else {
            $this->coverage_cities[] = $code;
            $this->travel_fees[$code] = $this->travel_fees[$code] ?? 0;
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
        unset($this->portfolio_urls[$index]);
        $this->portfolio_urls = array_values($this->portfolio_urls);
    }

    public function deleteExistingImage(int $index): void
    {
        if (! isset($this->portfolio_images_existing[$index])) {
            return;
        }
        $url = $this->portfolio_images_existing[$index];
        $this->deleteLocalStorageFromUrl($url);
        unset($this->portfolio_images_existing[$index]);
        $this->portfolio_images_existing = array_values($this->portfolio_images_existing);
    }

    public function deleteLogo(): void
    {
        if ($this->logo_url_existing) {
            $this->deleteLocalStorageFromUrl($this->logo_url_existing);
            $this->logo_url_existing = null;
        }
    }

    private function deleteLocalStorageFromUrl(string $url): void
    {
        $prefix = Storage::url('');
        if (str_starts_with($url, $prefix)) {
            $path = ltrim(substr($url, strlen($prefix)), '/');
            Storage::disk('public')->delete($path);
        }
    }

    protected function rules(): array
    {
        return [
            'full_name'            => ['required', 'string', 'min:2', 'max:100'],
            'phone'                => ['required', 'regex:' . PhoneFormat::REGEX],
            'whatsapp'             => ['required', 'regex:' . PhoneFormat::REGEX],
            'business_name'        => ['required', 'string', 'min:2', 'max:100'],
            'business_type'        => ['required', 'in:sole_proprietor,llc,corporation,individual'],
            'trade_register'       => ['nullable', 'string', 'max:50'],
            'years_experience'     => ['nullable', 'integer', 'min:0', 'max:60'],
            'business_description' => ['nullable', 'string', 'max:500'],
            'primary_city_code'  => ['required', 'exists:cities,code'],
            'price_tier'           => ['required', 'in:budget,mid,premium,luxury'],
            'min_price'            => ['nullable', 'integer', 'min:0'],
            'max_price'            => ['nullable', 'integer', 'min:0', 'gte:min_price'],
            'languages'            => ['required', 'array', 'min:1'],
            'languages.*'          => ['in:en,fr,es'],
            'services'             => ['required', 'array', 'min:1'],
            'services.*'           => ['in:' . implode(',', array_column(ServiceType::cases(), 'value'))],
            'coverage_cities'     => ['required', 'array', 'min:1'],
            'coverage_cities.*'   => ['exists:cities,code'],
            'travel_fees.*'        => ['integer', 'min:0'],
            'portfolio_urls'       => ['nullable', 'array', 'max:5'],
            'portfolio_urls.*'     => ['nullable', 'url', 'max:500'],
            'portfolio_images_new' => ['nullable', 'array', 'max:10'],
            'portfolio_images_new.*' => ['image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
            'logo_new'             => ['nullable', 'image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user) {
                $user->forceFill([
                    'name'        => $this->full_name,
                    'phone'       => $this->phone,
                    'whatsapp'    => $this->whatsapp,
                    'city_code' => $this->primary_city_code,
                ])->save();

                $profile = $user->providerProfile ?: new ProviderProfile(['user_id' => $user->id]);

                if ($this->logo_new instanceof TemporaryUploadedFile) {
                    if ($this->logo_url_existing) {
                        $this->deleteLocalStorageFromUrl($this->logo_url_existing);
                    }
                    $logoPath = $this->logo_new->storeAs(
                        "providers/{$user->id}",
                        'logo.' . $this->logo_new->getClientOriginalExtension(),
                        'public',
                    );
                    $this->logo_url_existing = Storage::url($logoPath);
                }

                $newImageUrls = [];
                foreach ((array) $this->portfolio_images_new as $img) {
                    if (! $img instanceof TemporaryUploadedFile) {
                        continue;
                    }
                    $path = $img->store("providers/{$user->id}/portfolio", 'public');
                    $newImageUrls[] = Storage::url($path);
                }

                $allImages = array_values(array_filter(array_merge($this->portfolio_images_existing, $newImageUrls)));
                $cleanUrls = array_values(array_filter($this->portfolio_urls));

                $profile->fill([
                    'business_name'        => $this->business_name,
                    'business_description' => $this->business_description ?: null,
                    'business_type'        => $this->business_type,
                    'trade_register'       => $this->trade_register ?: null,
                    'primary_city_code'  => $this->primary_city_code,
                    'price_tier'           => $this->price_tier,
                    'min_price'            => $this->min_price,
                    'max_price'            => $this->max_price,
                    'years_experience'     => $this->years_experience,
                    'portfolio_url'        => $cleanUrls[0] ?? null,
                    'portfolio_images'     => $allImages,
                    'logo_url'             => $this->logo_url_existing,
                    'languages'            => array_values($this->languages),
                    'onboarding_completed' => true,
                ]);
                $profile->save();

                ProviderService::where('user_id', $user->id)->delete();
                foreach (array_unique($this->services) as $svc) {
                    ProviderService::create(['user_id' => $user->id, 'service_type' => $svc]);
                }

                if (! in_array($this->primary_city_code, $this->coverage_cities, true)) {
                    $this->coverage_cities[] = $this->primary_city_code;
                }

                $wantedCodes = array_unique($this->coverage_cities);
                ProviderCoverage::where('user_id', $user->id)->whereNotIn('city_code', $wantedCodes)->delete();
                foreach ($wantedCodes as $code) {
                    $fee = $code === $this->primary_city_code ? 0 : (int) ($this->travel_fees[$code] ?? 0);
                    ProviderCoverage::updateOrCreate(
                        ['user_id' => $user->id, 'city_code' => $code],
                        ['travel_fee' => $fee],
                    );
                }
            });
        } catch (\Throwable $e) {
            report($e);
            $this->addError('submission', __('provider.profile.save_failed'));

            return null;
        }

        $this->portfolio_images_new = [];
        $this->logo_new = null;

        session()->flash('status', __('provider.profile.saved'));

        return null;
    }

    public function render()
    {
        return view('livewire.provider.profile-editor');
    }
}
