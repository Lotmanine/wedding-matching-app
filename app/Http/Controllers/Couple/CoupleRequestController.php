<?php

declare(strict_types=1);

namespace App\Http\Controllers\Couple;

use App\Http\Controllers\Controller;
use App\Models\WedRequest;
use Illuminate\View\View;

class CoupleRequestController extends Controller
{
    public function create(): View
    {
        return view('couple.request.create');
    }

    public function confirmation(string $wedUid): View
    {
        $wedRequest = WedRequest::where('wed_uid', $wedUid)
            ->with('serviceRequests')
            ->firstOrFail();

        return view('couple.request.confirmation', compact('wedRequest'));
    }
}
