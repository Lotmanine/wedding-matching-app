<?php

declare(strict_types=1);

namespace Tests\Feature\Matching;

use App\Enums\RequestStatus;
use App\Events\LeadOpenedToMarketplace;
use App\Events\RoundStarted;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Services\Matching\RoundStateMachine;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeBareLead(): ServiceRequest
    {
        $wr = WedRequest::factory()->create(['services_needed' => ['photo']]);

        return ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => RequestStatus::New->value,
            'current_round'  => 0,
        ]);
    }

    public function test_start_round_1_sets_status_and_merges_notified(): void
    {
        Event::fake([RoundStarted::class]);

        $lead = $this->makeBareLead();
        $sm = app(RoundStateMachine::class);

        $sm->startRound($lead, 1, [10, 20, 30]);
        $lead->refresh();

        $this->assertSame(RequestStatus::Round1, $lead->status);
        $this->assertSame(1, $lead->current_round);
        $this->assertEquals([10, 20, 30], $lead->round_1_provider_ids);
        $this->assertEquals([10, 20, 30], $lead->all_notified_provider_ids);
        $this->assertNotNull($lead->round_started_at);

        Event::assertDispatched(RoundStarted::class);
    }

    public function test_start_round_2_merges_into_all_notified(): void
    {
        $lead = $this->makeBareLead();
        $sm = app(RoundStateMachine::class);

        $sm->startRound($lead, 1, [10, 20]);
        $lead->refresh();
        $sm->startRound($lead, 2, [30, 40]);
        $lead->refresh();

        $this->assertSame(RequestStatus::Round2, $lead->status);
        $this->assertSame(2, $lead->current_round);
        $this->assertEquals([30, 40], $lead->round_2_provider_ids);
        $this->assertEquals([10, 20, 30, 40], $lead->all_notified_provider_ids);
    }

    public function test_advance_round_with_round_below_3_sets_matching(): void
    {
        $lead = $this->makeBareLead();
        $sm = app(RoundStateMachine::class);
        $sm->startRound($lead, 1, [10]);
        $lead->refresh();

        $sm->advanceRound($lead);

        $this->assertSame(RequestStatus::Matching, $lead->fresh()->status);
        $this->assertNull($lead->fresh()->round_started_at);
    }

    public function test_advance_round_at_3_opens_marketplace(): void
    {
        Event::fake([LeadOpenedToMarketplace::class, RoundStarted::class]);

        $lead = $this->makeBareLead();
        $sm = app(RoundStateMachine::class);
        $sm->startRound($lead, 1, [10]);
        $lead->refresh();
        $sm->startRound($lead, 2, [20]);
        $lead->refresh();
        $sm->startRound($lead, 3, [30]);
        $lead->refresh();

        $sm->advanceRound($lead);

        $lead->refresh();
        $this->assertSame(RequestStatus::OpenMarketplace, $lead->status);
        $this->assertSame(4, $lead->current_round);
        $this->assertNull($lead->round_started_at);

        Event::assertDispatched(LeadOpenedToMarketplace::class);
    }

    public function test_mark_accepted_sets_unlock_fields(): void
    {
        $user = User::factory()->create();
        $lead = $this->makeBareLead();

        app(RoundStateMachine::class)->markAccepted($lead, $user, 'api');

        $lead->refresh();
        $this->assertSame(RequestStatus::Accepted, $lead->status);
        $this->assertSame($user->id, $lead->unlocked_by_user_id);
        $this->assertSame('api', $lead->unlocked_via);
        $this->assertNotNull($lead->unlocked_at);
    }

    public function test_is_round_timed_out_reflects_window(): void
    {
        $lead = $this->makeBareLead();
        $sm = app(RoundStateMachine::class);

        $sm->startRound($lead, 1, [10]);
        $lead->refresh();
        $this->assertFalse($sm->isRoundTimedOut($lead));

        $lead->forceFill(['round_started_at' => now()->subMinutes(RoundStateMachine::ROUND_TIMEOUT_MINUTES + 1)])->save();
        $this->assertTrue($sm->isRoundTimedOut($lead->fresh()));
    }

    public function test_invalid_round_number_throws(): void
    {
        $lead = $this->makeBareLead();
        $this->expectException(\InvalidArgumentException::class);
        app(RoundStateMachine::class)->startRound($lead, 4, [1]);
    }
}
