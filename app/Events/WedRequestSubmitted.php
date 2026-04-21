<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\WedRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WedRequestSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public WedRequest $wedRequest)
    {
    }
}
