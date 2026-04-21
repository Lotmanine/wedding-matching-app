<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('providerProfile');

        return view('provider.dashboard', [
            'user'    => $user,
            'profile' => $user->providerProfile,
        ]);
    }
}
