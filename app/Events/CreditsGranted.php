<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\CreditLedger;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditsGranted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Purchase $purchase,
        public CreditLedger $ledger,
        public User $user,
    ) {
    }
}
