<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Enums\BudgetRange;
use App\Models\ServiceRequest;
use App\Models\WedRequest;
use App\Support\Whatsapp\RecipientPayloadBuilder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientPayloadBudgetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The budget hint is printed in the provider's WhatsApp message. The map
     * once drifted from the enum, leaving half of the ranges without a value.
     */
    public function test_every_budget_range_has_a_budget_hint(): void
    {
        $this->seed(CitySeeder::class);

        foreach (BudgetRange::cases() as $range) {
            $wr = WedRequest::factory()->create(['couple_city_code' => 'NY', 'budget_range' => $range->value]);
            $sr = ServiceRequest::create([
                'wed_request_id' => $wr->id,
                'service_type'   => 'photo',
                'status'         => 'new',
                'current_round'  => 0,
            ]);

            $hint = RecipientPayloadBuilder::servicePayload($sr->load('wedRequest'))['budget_hint'];

            $this->assertIsInt($hint, "No budget hint for {$range->value}");
            $this->assertGreaterThan(0, $hint);
        }
    }
}
