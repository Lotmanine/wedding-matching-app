<?php

declare(strict_types=1);

namespace App\Services\Credits;

use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditLedger;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditsService
{
    public function deduct(
        User $user,
        int $amount,
        string $type = 'unlock',
        ?Model $reference = null,
        ?string $notes = null,
    ): CreditLedger {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive; use add() for credits.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $reference, $notes) {
            $profile = ProviderProfile::where('user_id', $user->id)->lockForUpdate()->first();

            $balance = (int) ($profile?->credit_balance ?? 0);
            if ($balance < $amount) {
                throw new InsufficientCreditsException($amount, $balance);
            }

            $newBalance = $balance - $amount;

            $ledger = CreditLedger::create([
                'user_id'        => $user->id,
                'delta'          => -$amount,
                'balance_after'  => $newBalance,
                'type'           => $type,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id'   => $reference?->getKey(),
                'notes'          => $notes,
            ]);

            $profile?->forceFill(['credit_balance' => $newBalance])->save();

            return $ledger;
        });
    }

    public function add(
        User $user,
        int $amount,
        string $type = 'bonus',
        ?Model $reference = null,
        ?string $notes = null,
    ): CreditLedger {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $reference, $notes) {
            $profile = ProviderProfile::where('user_id', $user->id)->lockForUpdate()->first();

            $balance = (int) ($profile?->credit_balance ?? 0);
            $newBalance = $balance + $amount;

            $ledger = CreditLedger::create([
                'user_id'        => $user->id,
                'delta'          => $amount,
                'balance_after'  => $newBalance,
                'type'           => $type,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id'   => $reference?->getKey(),
                'notes'          => $notes,
            ]);

            $profile?->forceFill(['credit_balance' => $newBalance])->save();

            return $ledger;
        });
    }

    public function getBalance(User $user): int
    {
        $profile = $user->providerProfile ?? $user->loadMissing('providerProfile')->providerProfile;

        return (int) ($profile?->credit_balance ?? 0);
    }
}
