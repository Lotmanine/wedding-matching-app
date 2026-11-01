<?php

declare(strict_types=1);

namespace Tests\Feature\Credits;

use App\Models\CreditLedger;
use App\Models\ProviderProfile;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function makeProvider(): User
    {
        $user = User::factory()->create(['role' => 'provider']);
        $user->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_city_code' => 'NY',
            'verified'            => true,
            'languages'           => ['fr'],
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
        ]);

        return $user;
    }

    public function test_provider_sees_only_their_own_transactions(): void
    {
        $me = $this->makeProvider();
        $other = $this->makeProvider();

        CreditLedger::create(['user_id' => $me->id,    'delta' => 3, 'balance_after' => 3, 'type' => 'bonus', 'notes' => 'my-bonus']);
        CreditLedger::create(['user_id' => $other->id, 'delta' => 5, 'balance_after' => 5, 'type' => 'bonus', 'notes' => 'their-bonus']);

        $this->actingAs($me)->get(route('provider.credits.history'))
            ->assertOk()
            ->assertSee('my-bonus')
            ->assertDontSee('their-bonus');
    }

    public function test_type_filter_narrows_results(): void
    {
        $me = $this->makeProvider();
        CreditLedger::create(['user_id' => $me->id, 'delta' => 3,  'balance_after' => 3, 'type' => 'bonus',    'notes' => 'entry-bonus']);
        CreditLedger::create(['user_id' => $me->id, 'delta' => -1, 'balance_after' => 2, 'type' => 'unlock',   'notes' => 'entry-unlock']);
        CreditLedger::create(['user_id' => $me->id, 'delta' => 6,  'balance_after' => 8, 'type' => 'purchase', 'notes' => 'entry-purchase']);

        $this->actingAs($me)->get(route('provider.credits.history', ['type' => 'purchase']))
            ->assertOk()
            ->assertSee('entry-purchase')
            ->assertDontSee('entry-bonus')
            ->assertDontSee('entry-unlock');
    }

    public function test_csv_export_returns_only_own_rows(): void
    {
        $me = $this->makeProvider();
        $other = $this->makeProvider();

        CreditLedger::create(['user_id' => $me->id,    'delta' => 3, 'balance_after' => 3, 'type' => 'bonus', 'notes' => 'my-csv-line']);
        CreditLedger::create(['user_id' => $other->id, 'delta' => 5, 'balance_after' => 5, 'type' => 'bonus', 'notes' => 'their-csv-line']);

        $response = $this->actingAs($me)->get(route('provider.credits.history', ['format' => 'csv']));
        $response->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Date,Type', $csv);
        $this->assertStringContainsString('my-csv-line', $csv);
        $this->assertStringNotContainsString('their-csv-line', $csv);
    }
}
