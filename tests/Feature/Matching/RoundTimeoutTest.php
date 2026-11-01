<?php

declare(strict_types=1);

namespace Tests\Feature\Matching;

use App\Enums\RequestStatus;
use App\Events\LeadOpenedToMarketplace;
use App\Events\RoundTimedOut;
use App\Models\ServiceRequest;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RoundTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeLeadAtRound(int $round, ?\DateTimeInterface $startedAt = null): ServiceRequest
    {
        $wr = WedRequest::factory()->create(['services_needed' => ['photo']]);
        $status = match ($round) {
            1 => RequestStatus::Round1->value,
            2 => RequestStatus::Round2->value,
            3 => RequestStatus::Round3->value,
        };

        return ServiceRequest::create([
            'wed_request_id'       => $wr->id,
            'service_type'         => 'photo',
            'status'               => $status,
            'current_round'        => $round,
            'round_started_at'     => $startedAt ?? now()->subHours(3),
            'round_1_provider_ids' => $round >= 1 ? [101, 102] : null,
            'round_2_provider_ids' => $round >= 2 ? [103, 104] : null,
            'round_3_provider_ids' => $round >= 3 ? [105, 106] : null,
        ]);
    }

    public function test_round_1_timeout_advances_to_matching(): void
    {
        Event::fake([RoundTimedOut::class, LeadOpenedToMarketplace::class]);

        $lead = $this->makeLeadAtRound(1);

        Artisan::call('wedding:advance-rounds');

        $lead->refresh();
        $this->assertSame(RequestStatus::Matching, $lead->status);
        $this->assertSame(1, $lead->current_round);
        $this->assertNull($lead->round_started_at);

        Event::assertDispatched(RoundTimedOut::class);
    }

    public function test_round_3_timeout_opens_to_marketplace(): void
    {
        Event::fake([RoundTimedOut::class, LeadOpenedToMarketplace::class]);

        $lead = $this->makeLeadAtRound(3);

        Artisan::call('wedding:advance-rounds');

        $lead->refresh();
        $this->assertSame(RequestStatus::OpenMarketplace, $lead->status);
        $this->assertSame(4, $lead->current_round);

        Event::assertDispatched(RoundTimedOut::class);
        Event::assertDispatched(LeadOpenedToMarketplace::class);
    }

    public function test_non_timed_out_round_not_advanced(): void
    {
        $lead = $this->makeLeadAtRound(1, now()->subMinutes(10));

        Artisan::call('wedding:advance-rounds');

        $lead->refresh();
        $this->assertSame(RequestStatus::Round1, $lead->status);
        $this->assertNotNull($lead->round_started_at);
    }

    public function test_already_unlocked_lead_not_affected(): void
    {
        $user = \App\Models\User::factory()->create(['role' => 'provider']);
        $lead = $this->makeLeadAtRound(3);
        $lead->forceFill(['unlocked_by_user_id' => $user->id, 'unlocked_at' => now()])->save();

        Artisan::call('wedding:advance-rounds');

        $lead->refresh();
        $this->assertSame(RequestStatus::Round3, $lead->status);
    }
}
