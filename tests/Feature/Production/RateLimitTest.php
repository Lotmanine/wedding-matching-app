<?php

declare(strict_types=1);

namespace Tests\Feature\Production;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_provider_login_rate_limit_blocks_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/provider/login', [
                'email'    => 'fake@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/provider/login', [
            'email'    => 'fake@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_admin_login_rate_limit_applied(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', [
                'email'    => 'nope@example.com',
                'password' => 'nope',
            ]);
        }

        $this->post('/admin/login', [
            'email'    => 'nope@example.com',
            'password' => 'nope',
        ])->assertStatus(429);
    }
}
