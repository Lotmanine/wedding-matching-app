<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProviderLoginController extends Controller
{
    public function create(): View
    {
        return view('provider.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('provider.auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (! $user->hasRole('provider')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('provider.auth.not_provider'),
            ]);
        }

        $user->forceFill(['last_active_at' => now()])->save();

        $request->session()->regenerate();

        return redirect()->intended(route('provider.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
