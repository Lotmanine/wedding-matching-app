<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['providerProfile', 'providerServices', 'providerCoverage']);

        return view('provider.profile.edit', [
            'user' => $user,
        ]);
    }
}
