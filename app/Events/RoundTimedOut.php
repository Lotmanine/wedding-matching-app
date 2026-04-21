<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundTimedOut
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public int $previousRound,
    ) {
    }
}
