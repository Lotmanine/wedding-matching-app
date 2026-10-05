<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\N8n;

use App\Models\User;
use App\Services\Whatsapp\WhatsappSettings;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WhatsappConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function actingWithAbilities(array $abilities): void
    {
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, $abilities);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->getJson('/api/v1/n8n/whatsapp-config')->assertStatus(401);
    }

    public function test_token_without_n8n_ability_is_forbidden(): void
    {
        $this->actingWithAbilities(['something-else']);

        $this->getJson('/api/v1/n8n/whatsapp-config')->assertStatus(403);
    }

    public function test_unconfigured_returns_disabled_and_empty_values(): void
    {
        $this->actingWithAbilities(['n8n-service']);

        $this->getJson('/api/v1/n8n/whatsapp-config')
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.phone_number_id', '')
            ->assertJsonPath('data.access_token', '')
            ->assertJsonPath('data.verify_token', '')
            ->assertJsonPath('data.app_secret', '');
    }

    public function test_returns_decrypted_values_when_configured(): void
    {
        app(WhatsappSettings::class)->save([
            'enabled'         => '1',
            'phone_number_id' => '123456789012345',
            'access_token'    => 'EAAG-access-token-1234',
            'verify_token'    => 'verify-token-5678',
            'app_secret'      => 'app-secret-9012',
        ]);

        $this->actingWithAbilities(['n8n-service']);

        $this->getJson('/api/v1/n8n/whatsapp-config')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.phone_number_id', '123456789012345')
            ->assertJsonPath('data.access_token', 'EAAG-access-token-1234')
            ->assertJsonPath('data.verify_token', 'verify-token-5678')
            ->assertJsonPath('data.app_secret', 'app-secret-9012');
    }

    public function test_switch_off_reports_disabled_but_keeps_inbound_secrets(): void
    {
        app(WhatsappSettings::class)->save([
            'phone_number_id' => '123456789012345',
            'access_token'    => 'EAAG-access-token-1234',
            'verify_token'    => 'verify-token-5678',
            'app_secret'      => 'app-secret-9012',
        ]);

        $this->actingWithAbilities(['n8n-service']);

        $this->getJson('/api/v1/n8n/whatsapp-config')
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.verify_token', 'verify-token-5678')
            ->assertJsonPath('data.app_secret', 'app-secret-9012');
    }
}
