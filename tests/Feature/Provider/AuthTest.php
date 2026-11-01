<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/provider/login')->assertOk()->assertSee('provider area');
    }

    public function test_login_with_valid_provider_credentials_succeeds(): void
    {
        $user = User::factory()->create([
            'email'    => 'p@provider.example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'provider',
        ]);
        $user->assignRole('provider');

        $this->post('/provider/login', [
            'email'    => 'p@provider.example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('provider.dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_non_provider_account(): void
    {
        $user = User::factory()->create([
            'email'    => 'c@couple.example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'couple',
        ]);
        $user->assignRole('couple');

        $this->from('/provider/login')
            ->post('/provider/login', [
                'email'    => 'c@couple.example.com',
                'password' => 'secret123',
            ])
            ->assertRedirect('/provider/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $user = User::factory()->create(['role' => 'provider']);
        $user->assignRole('provider');

        $this->actingAs($user)->post('/provider/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_password_reset_email_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@provider.example.com', 'role' => 'provider']);
        $user->assignRole('provider');

        $this->post('/provider/forgot-password', ['email' => 'reset@provider.example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
