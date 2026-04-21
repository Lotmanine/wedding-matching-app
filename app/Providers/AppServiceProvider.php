<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        RateLimiter::for('form-submission', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return URL::to(route('provider.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false));
        });
    }
}
