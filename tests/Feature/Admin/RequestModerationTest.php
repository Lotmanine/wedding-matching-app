<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RequestStatus;
use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function admin(): User
    {
        $a = User::factory()->create(['role' => 'admin']);
        $a->assignRole('admin');

        return $a;
    }

    public function test_expire_marks_wed_request_expired(): void
    {
        $wr = WedRequest::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.requests.expire', $wr->wed_uid))->assertRedirect();

        $wr->refresh();
        $this->assertSame(RequestStatus::Expired, $wr->status);
        $this->assertTrue($wr->expires_at->isPast());
        $this->assertTrue(AuditLog::where('action', 'wed_request.expire')->exists());
    }

    public function test_delete_requires_typed_confirmation(): void
    {
        $wr = WedRequest::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.requests.delete', $wr->wed_uid), ['reason' => 'x'])
            ->assertSessionHasErrors('confirm');

        $this->assertNotNull(WedRequest::find($wr->id));
    }

    public function test_delete_force_deletes_with_valid_confirmation(): void
    {
        $wr = WedRequest::factory()->create();
        ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => 'round1',
            'current_round'  => 1,
            'round_started_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.requests.delete', $wr->wed_uid), ['confirm' => 'SUPPRIMER', 'reason' => 'abuse'])
            ->assertRedirect(route('admin.requests.index'));

        $this->assertNull(WedRequest::withTrashed()->find($wr->id));
        $this->assertSame(0, ServiceRequest::withTrashed()->where('wed_request_id', $wr->id)->count());
        $this->assertTrue(AuditLog::where('action', 'wed_request.delete')->exists());
    }

    public function test_force_open_to_marketplace(): void
    {
        $wr = WedRequest::factory()->create();
        $sr = ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => 'round1',
            'current_round'  => 1,
            'round_started_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.service_requests.force_open', $sr->service_uid))
            ->assertRedirect();

        $sr->refresh();
        $this->assertSame(RequestStatus::OpenMarketplace, $sr->status);
        $this->assertSame(4, $sr->current_round);
    }
}
