<?php

declare(strict_types=1);

namespace Tests\Feature\Credits;

use App\Exceptions\InsufficientCreditsException;
use App\Models\CreditLedger;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\Credits\CreditsService;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function makeUser(int $balance = 0): User
    {
        $user = User::factory()->create(['role' => 'provider']);
        $user->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'credit_balance'      => $balance,
        ]);

        return $user->fresh('providerProfile');
    }

    public function test_add_writes_ledger_and_updates_balance(): void
    {
        $user = $this->makeUser(balance: 2);
        $svc = app(CreditsService::class);

        $ledger = $svc->add($user, 5, 'bonus', null, 'Promo');

        $this->assertSame(5, $ledger->delta);
        $this->assertSame(7, $ledger->balance_after);
        $this->assertSame('bonus', $ledger->type);

        $user->refresh()->load('providerProfile');
        $this->assertSame(7, $user->providerProfile->credit_balance);
    }

    public function test_deduct_writes_ledger_and_updates_balance(): void
    {
        $user = $this->makeUser(balance: 3);
        $svc = app(CreditsService::class);

        $ledger = $svc->deduct($user, 2, 'unlock');

        $this->assertSame(-2, $ledger->delta);
        $this->assertSame(1, $ledger->balance_after);

        $user->refresh()->load('providerProfile');
        $this->assertSame(1, $user->providerProfile->credit_balance);
    }

    public function test_deduct_throws_when_balance_insufficient(): void
    {
        $user = $this->makeUser(balance: 1);
        $svc = app(CreditsService::class);

        $this->expectException(InsufficientCreditsException::class);
        $svc->deduct($user, 5, 'unlock');
    }

    public function test_insufficient_deduct_leaves_no_ledger_row(): void
    {
        $user = $this->makeUser(balance: 0);
        $svc = app(CreditsService::class);

        try {
            $svc->deduct($user, 1, 'unlock');
        } catch (InsufficientCreditsException) {
            // expected
        }

        $this->assertSame(0, CreditLedger::where('user_id', $user->id)->count());
        $user->refresh()->load('providerProfile');
        $this->assertSame(0, $user->providerProfile->credit_balance);
    }

    public function test_getBalance_returns_profile_balance(): void
    {
        $user = $this->makeUser(balance: 42);
        $this->assertSame(42, app(CreditsService::class)->getBalance($user));
    }
}
