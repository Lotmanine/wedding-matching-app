<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Whatsapp\WhatsappSettings;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);
        $user->assignRole($role);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'enabled'         => '1',
            'phone_number_id' => '123456789012345',
            'access_token'    => 'EAAG-access-token-1234',
            'verify_token'    => 'verify-token-5678',
            'app_secret'      => 'app-secret-9012',
        ], $overrides);
    }

    public function test_admin_can_view_the_page(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.whatsapp.edit'))
            ->assertOk()
            ->assertSee('WhatsApp est désactivé.');
    }

    public function test_manager_is_forbidden(): void
    {
        $manager = $this->userWithRole('manager');

        $this->actingAs($manager)->get(route('admin.whatsapp.edit'))->assertForbidden();
        $this->actingAs($manager)->put(route('admin.whatsapp.update'), $this->validPayload())->assertForbidden();
    }

    public function test_update_stores_secrets_encrypted_and_activates(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->put(route('admin.whatsapp.update'), $this->validPayload())
            ->assertRedirect();

        $raw = (string) Setting::where('key', 'whatsapp.access_token')->value('value');
        $this->assertNotSame('', $raw);
        $this->assertStringNotContainsString('EAAG-access-token-1234', $raw);

        $settings = app(WhatsappSettings::class);
        $this->assertSame('EAAG-access-token-1234', $settings->accessToken());
        $this->assertSame('verify-token-5678', $settings->verifyToken());
        $this->assertSame('app-secret-9012', $settings->appSecret());
        $this->assertSame('123456789012345', $settings->phoneNumberId());
        $this->assertTrue($settings->isActive());
    }

    public function test_blank_secret_fields_keep_current_values(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put(route('admin.whatsapp.update'), $this->validPayload());
        $this->actingAs($admin)->put(route('admin.whatsapp.update'), $this->validPayload([
            'access_token' => '',
            'verify_token' => '',
            'app_secret'   => '',
        ]))->assertRedirect();

        $settings = app(WhatsappSettings::class);
        $this->assertSame('EAAG-access-token-1234', $settings->accessToken());
        $this->assertSame('verify-token-5678', $settings->verifyToken());
        $this->assertSame('app-secret-9012', $settings->appSecret());
    }

    public function test_switch_on_without_keys_is_not_active(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->put(route('admin.whatsapp.update'), ['enabled' => '1'])
            ->assertRedirect();

        $settings = app(WhatsappSettings::class);
        $this->assertTrue($settings->switchedOn());
        $this->assertFalse($settings->isActive());
    }

    public function test_phone_number_id_must_be_numeric(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->put(route('admin.whatsapp.update'), $this->validPayload(['phone_number_id' => '+213 661 23 45 67']))
            ->assertSessionHasErrors('phone_number_id');
    }

    public function test_page_and_audit_log_never_expose_secrets(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put(route('admin.whatsapp.update'), $this->validPayload());

        $this->actingAs($admin)->get(route('admin.whatsapp.edit'))
            ->assertOk()
            ->assertSee('WhatsApp est actif.')
            ->assertDontSee('EAAG-access-token-1234')
            ->assertDontSee('app-secret-9012');

        $log = AuditLog::where('action', 'whatsapp.settings.update')->firstOrFail();
        $encoded = (string) json_encode($log->metadata);
        $this->assertStringNotContainsString('EAAG-access-token-1234', $encoded);
        $this->assertSame('updated', $log->metadata['access_token']);
    }
}
