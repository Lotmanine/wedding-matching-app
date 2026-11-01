<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\UnlockPack;
use App\Models\User;
use App\Services\Payments\PurchaseService;
use Illuminate\Console\Command;

class SimulatePurchase extends Command
{
    protected $signature = 'wedding:simulate-purchase {email} {pack_slug}';

    protected $description = 'Bypass Chargily — create + mark-paid a purchase so credits land in the provider balance (local dev only).';

    public function handle(PurchaseService $purchases): int
    {
        $email = (string) $this->argument('email');
        $slug = (string) $this->argument('pack_slug');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user with email {$email}.");

            return self::FAILURE;
        }

        $pack = UnlockPack::where('slug', $slug)->first();
        if (! $pack) {
            $this->error("Pack {$slug} not found.");

            return self::FAILURE;
        }

        $purchase = $purchases->initiate($user, $pack);
        $purchase->forceFill([
            'gateway_checkout_id' => 'sim-' . strtolower(bin2hex(random_bytes(6))),
            'gateway_payment_id'  => 'sim-pay-' . strtolower(bin2hex(random_bytes(6))),
        ])->save();

        $purchases->markPaid($purchase, ['simulated' => true]);

        $this->info("Simulated purchase {$purchase->purchase_uid} paid — {$pack->unlocks_count} credits granted to {$email}.");

        $user->refresh()->load('providerProfile');
        $this->line("New balance: {$user->providerProfile?->credit_balance}");

        return self::SUCCESS;
    }
}
