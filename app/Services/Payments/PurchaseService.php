<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Events\CreditsGranted;
use App\Models\CreditLedger;
use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Models\User;
use App\Services\Credits\CreditsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private readonly CreditsService $credits)
    {
    }

    public function initiate(User $user, UnlockPack $pack): Purchase
    {
        return Purchase::create([
            'user_id'        => $user->id,
            'unlock_pack_id' => $pack->id,
            'amount'         => (int) $pack->price,
            'currency'       => $user->providerProfile?->currency() ?? 'USD',
            'unlocks_count'  => (int) $pack->unlocks_count,
            'status'         => 'pending',
            'gateway'        => 'chargily',
            'payment_method' => 'online_chargily',
        ]);
    }

    /**
     * Record an offline (bank transfer / CCP) purchase. No credits are granted
     * yet — the purchase sits in 'pending_verification' until an admin confirms
     * the funds arrived and marks it paid.
     */
    public function initiateOffline(User $user, UnlockPack $pack, string $reference, ?string $proofPath = null): Purchase
    {
        return Purchase::create([
            'user_id'           => $user->id,
            'unlock_pack_id'    => $pack->id,
            'amount'            => (int) $pack->price,
            'currency'          => $user->providerProfile?->currency() ?? 'USD',
            'unlocks_count'     => (int) $pack->unlocks_count,
            'status'            => 'pending_verification',
            'gateway'           => 'offline',
            'payment_method'    => 'offline_versement',
            'payment_reference' => $reference,
            'proof_path'        => $proofPath,
        ]);
    }

    public function markPaid(Purchase $purchase, array $gatewayPayload = []): Purchase
    {
        return DB::transaction(function () use ($purchase, $gatewayPayload) {
            $locked = Purchase::where('id', $purchase->id)->lockForUpdate()->first();

            if ($locked->status === 'paid') {
                return $locked;
            }

            $paymentId = $gatewayPayload['payment_id']
                ?? ($gatewayPayload['id'] ?? null);

            $locked->forceFill([
                'status'             => 'paid',
                'paid_at'            => now(),
                'gateway_payment_id' => $paymentId,
                'gateway_metadata'   => $gatewayPayload,
            ])->save();

            $ledger = $this->grantCredits($locked);

            $locked->forceFill([
                'credit_ledger_id'   => $ledger->id,
                'credits_granted_at' => now(),
            ])->save();

            Cache::forget("leads:count:{$locked->user_id}");

            CreditsGranted::dispatch($locked, $ledger, $locked->user);

            return $locked;
        });
    }

    public function markFailed(Purchase $purchase, string $reason, array $gatewayPayload = []): Purchase
    {
        return DB::transaction(function () use ($purchase, $reason, $gatewayPayload) {
            $locked = Purchase::where('id', $purchase->id)->lockForUpdate()->first();

            if (in_array($locked->status, ['paid', 'failed', 'cancelled'], true)) {
                return $locked;
            }

            $locked->forceFill([
                'status'           => $reason === 'user_cancelled' ? 'cancelled' : 'failed',
                'failed_at'        => now(),
                'failure_reason'   => $reason,
                'gateway_metadata' => $gatewayPayload,
            ])->save();

            return $locked;
        });
    }

    public function grantCredits(Purchase $purchase): CreditLedger
    {
        $purchase->loadMissing('unlockPack', 'user');

        $notes = 'Achat ' . ($purchase->unlockPack?->name ?? 'pack') . ' — ' . $purchase->purchase_uid;

        return $this->credits->add(
            user: $purchase->user,
            amount: (int) $purchase->unlocks_count,
            type: 'purchase',
            reference: $purchase,
            notes: $notes,
        );
    }
}
