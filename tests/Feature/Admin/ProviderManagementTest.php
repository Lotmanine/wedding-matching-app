<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\CreditLedger;
use App\Models\ProviderProfile;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        return $admin;
    }

    private function makeProvider(int $balance = 5): User
    {
        $user = User::factory()->create(['role' => 'provider', 'status' => 'active']);
        $user->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_wilaya_code' => '16',
            'verified'            => false,
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'credit_balance'      => $balance,
        ]);

        return $user->fresh('providerProfile');
    }

    public function test_verify_sets_flag_and_logs(): void
    {
        $admin = $this->makeAdmin();
        $prov = $this->makeProvider();

        $this->actingAs($admin)->post(route('admin.providers.verify', $prov->id))->assertRedirect();

        $prov->refresh()->load('providerProfile');
        $this->assertTrue((bool) $prov->providerProfile->verified);
        $this->assertNotNull($prov->providerProfile->verified_at);
        $this->assertTrue(AuditLog::where('action', 'provider.verify')->exists());
    }

    public function test_suspend_requires_reason_and_logs(): void
    {
        $admin = $this->makeAdmin();
        $prov = $this->makeProvider();

        $this->actingAs($admin)->post(route('admin.providers.suspend', $prov->id), [])->assertSessionHasErrors('reason');

        $this->actingAs($admin)->post(route('admin.providers.suspend', $prov->id), ['reason' => 'TOS violation'])
            ->assertRedirect();

        $prov->refresh()->load('providerProfile');
        $this->assertNotNull($prov->providerProfile->suspended_at);
        $this->assertSame('suspended', $prov->fresh()->status);
        $this->assertTrue(AuditLog::where('action', 'provider.suspend')->exists());
    }

    public function test_reactivate_clears_suspension(): void
    {
        $admin = $this->makeAdmin();
        $prov = $this->makeProvider();
        $prov->providerProfile->forceFill(['suspended_at' => now(), 'suspended_reason' => 'test'])->save();
        $prov->update(['status' => 'suspended']);

        $this->actingAs($admin)->post(route('admin.providers.reactivate', $prov->id))->assertRedirect();

        $prov->refresh()->load('providerProfile');
        $this->assertNull($prov->providerProfile->suspended_at);
        $this->assertSame('active', $prov->status);
    }

    public function test_adjust_credits_adds_and_writes_ledger(): void
    {
        $admin = $this->makeAdmin();
        $prov = $this->makeProvider(balance: 2);

        $this->actingAs($admin)
            ->post(route('admin.providers.credits.adjust', $prov->id), [
                'amount' => 5,
                'reason' => 'dev bonus',
            ])
            ->assertRedirect();

        $prov->refresh()->load('providerProfile');
        $this->assertSame(7, $prov->providerProfile->credit_balance);

        $row = CreditLedger::where('user_id', $prov->id)->where('type', 'admin_adjustment')->first();
        $this->assertNotNull($row);
        $this->assertSame(5, $row->delta);
        $this->assertSame($admin->id, $row->created_by_user_id);
        $this->assertTrue(AuditLog::where('action', 'provider.credits.adjust')->exists());
    }

    public function test_adjust_credits_deducts_and_rejects_overdraft(): void
    {
        $admin = $this->makeAdmin();
        $prov = $this->makeProvider(balance: 2);

        $this->actingAs($admin)
            ->post(route('admin.providers.credits.adjust', $prov->id), [
                'amount' => -10,
                'reason' => 'claim fraud',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertSame(2, $prov->fresh()->load('providerProfile')->providerProfile->credit_balance);
    }
}
