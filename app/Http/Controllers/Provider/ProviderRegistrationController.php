<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProviderRegistrationController extends Controller
{
    public function create(): View
    {
        return view('provider.auth.register');
    }
}
