<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use App\Support\SettingsConfig;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Values saved in /admin/settings override config/zawaj.php.
        try {
            SettingsConfig::apply();
        } catch (\Throwable) {
            // Database not reachable yet (first deploy, config:cache…): keep defaults.
        }
        Event::listen(JobProcessing::class, fn () => SettingsConfig::apply());

        RateLimiter::for('form-submission', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(5)->by($request->ip() . '|' . $email);
        });

        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->currentAccessToken()?->id
                ?? ('ip:' . $request->ip());

            return Limit::perMinute(120)->by((string) $key);
        });

        RateLimiter::for('api-mutations', function (Request $request) {
            $key = $request->user()?->currentAccessToken()?->id
                ?? ('ip:' . $request->ip());

            return Limit::perMinute(30)->by((string) $key);
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return URL::to(route('provider.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false));
        });

        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\WebhookEventListener::class);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\RoundStarted::class,
            \App\Listeners\Whatsapp\DispatchRoundStartedWebhook::class,
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\LeadAccepted::class,
            \App\Listeners\Whatsapp\DispatchLeadAcceptedWebhook::class,
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\LeadOpenedToMarketplace::class,
            \App\Listeners\Whatsapp\DispatchLeadOpenedToMarketplaceWebhook::class,
        );
    }
}
