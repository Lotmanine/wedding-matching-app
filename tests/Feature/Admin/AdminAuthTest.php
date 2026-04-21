<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Admin');
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        $admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => Hash::make('pw'),
            'role'     => 'admin',
        ]);
        $admin->assignRole('admin');

        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'pw'])
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_non_admin_cannot_login_to_admin(): void
    {
        $provider = User::factory()->create([
            'email'    => 'prov@example.com',
            'password' => Hash::make('pw'),
            'role'     => 'provider',
        ]);
        $provider->assignRole('provider');

        $this->post('/admin/login', ['email' => 'prov@example.com', 'password' => 'pw'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_provider_role_forbidden_on_admin_dashboard(): void
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $provider->assignRole('provider');

        $this->actingAs($provider)->get('/admin')->assertForbidden();
    }

    public function test_logout_redirects_to_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin)->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
